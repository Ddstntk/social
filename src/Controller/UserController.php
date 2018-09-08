<?php
/**
 * User controller.
 *
 * @author    Konrad Szewczuk
 * @copyright (c) 2018 Konrad Szewczuk
 * @category  Social Media
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 *
 * Collage project - social network
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Repository\UserRepository;
use Repository\FriendsRepository;

use Form\SignupType;
use Form\EditType;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * Class UserController.
 */
class UserController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/profile', [$this, 'profileAction'])->bind('user_profile');
        $controller->get('/view/{id}', [$this, 'viewAction'])->bind('user_view');
        $controller->get('/index', [$this, 'indexAction'])->bind('users_index_paginated');
        $controller->match('/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->bind('user_edit');
        return $controller;
    }

    /**
     * @param Application $app
     * @param int         $page
     * @return mixed
     */
    public function indexAction(Application $app, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);
        $friendsRepository = new FriendsRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        return $app['twig']->render(
            'user/index.html.twig',
            ['paginator' => $userRepository->findAllPaginated($page, $friendsRepository, $userId)]
        );
    }

    /**
     * @param Application $app
     * @param $id
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function viewAction(Application $app, $id)
    {
        $userRepository = new UserRepository($app['db']);
        return $app['twig']->render(
            'user/view.html.twig',
            ['user' => $userRepository->getUserById($id)]
        );
    }

    /**
     * @param Application $app
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function profileAction(Application $app)
    {
        $userRepository = new UserRepository($app['db']);

        $id = $app['security.token_storage']->getToken()->getUser()->getID();
        var_dump($id);
        return $app['twig']->render(
            'user/view.html.twig',
            ['user' => $userRepository->getUserById($id)]
        );
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */

    public function editAction(Application $app, Request $request)
    {
        $user = [];

        $form = $app['form.factory']->createBuilder(
            EditType::class,
            $user
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = new UserRepository($app['db']);

            $user = $form->getData();
            $password = $user['password'];
            $user['password'] = $app['security.encoder.bcrypt']->encodePassword(
                $password,
                ''
            );
            $userRepository->save($user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('user_profile'), 301);
        }


        return $app['twig']->render(
            'user/edit.html.twig',
            array('form' => $form->createView())
        );
    }
}
