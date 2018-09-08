<?php
/**
 * Friends controller.
 *
 * @copyright (c) 2018 Konrad Szewczuk
 *
 * @link http://cis.wzks.uj.edu.pl/~16_szewczuk/web/
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Repository\FriendsRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * Class FriendsController.
 *
 * @author    Konrad Szewczuk
 * @copyright (c) 2018 Konrad Szewczuk
 * @category  Social Media
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 *
 * Collage project - social network
 */
class FriendsController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/invite/{friendId}', [$this, 'inviteAction'])->bind('friend_invite');
        $controller->get('/add/{friendId}', [$this, 'addFriend'])->bind('friend_add');
        $controller->get('/index', [$this, 'indexAction'])->bind('friends_index_paginated');
        $controller->get('/invites', [$this, 'indexInvites'])->bind('invites_index_paginated');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('friends_delete');
        return $controller;
    }

    public function inviteAction(Application $app, $friendId, $page = 1)
    {
        $friendsRepository = new FriendsRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();

        $friendsRepository -> invite($userId, $friendId);

        return $app['twig']->render(
            'friends/index.html.twig',
            ['paginator' => $friendsRepository->findAllPaginated($page, $userId)]
        );
    }

    /**
     * @param Application $app
     * @param $friendId
     * @param int         $page
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */

    public function addFriend(Application $app, $friendId, $page = 1)
    {
        $friendsRepository = new FriendsRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();

        $friendsRepository -> addFriend($userId, $friendId);

        return $app['twig']->render(
            'friends/index.html.twig',
            ['paginator' => $friendsRepository->findAllPaginated($page, $userId)]
        );
    }

    /**
     * @param Application $app
     * @param int         $page
     * @return mixed
     */
    public function indexAction(Application $app, $page = 1)
    {

        $friendsRepository = new FriendsRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        return $app['twig']->render(
            'friends/index.html.twig',
            ['paginator' => $friendsRepository->findAllPaginated($page, $userId)]
        );
    }

    /**
     * @param Application $app
     * @param int         $page
     * @return mixed
     */
    public function indexInvites(Application $app, $page = 1)
    {
        $friendsRepository = new FriendsRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        return $app['twig']->render(
            'friends/invites.html.twig',
            ['paginator' => $friendsRepository->findAllInvitesPaginated($page, $userId)]
        );
    }

    /**
     * @param Application $app
     * @param $id
     * @return mixed
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function deleteAction(Application $app, $id)
    {
        $friendsRepository = new FriendsRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        $friendsRepository -> delete($userId, $id);

        return $app['twig']->render(
            'friends/index.html.twig',
            ['paginator' => $friendsRepository->findAllPaginated(1, $userId)]
        );
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
