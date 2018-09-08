<?php
/**
 * Auth controller.
 */
namespace Controller;


use Form\LoginType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Repository\UserRepository;
use Form\SignupType;
use Service\userTokenService;
/**
 * Class AuthController.
 *
 * @author    Konrad Szewczuk
 * @copyright (c) 2018 Konrad Szewczuk
 * @category  Social Media
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 *
 * Collage project - social network
 */
class AuthController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->match('/login', [$this, 'loginAction'])
            ->method('GET|POST')
            ->bind('auth_login');
        $controller->get('/logout', [$this, 'logoutAction'])
            ->bind('auth_logout');
        $controller->get('/signup', [$this, 'signupAction'])
            ->method('POST|GET')
            ->bind('user_add');

        return $controller;
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @return mixed
     */
    public function loginAction(Application $app, Request $request)
    {
        $user = ['email' => $app['session']->get('_security.last_username')];
        $form = $app['form.factory']->createBuilder(LoginType::class, $user)->getForm();
        $app['session']->set('userid', $user);
        return $app['twig']->render(
            'auth/login.html.twig',
            [
                'form' => $form->createView(),
                'error' => $app['security.last_error']($request),
            ]
        );
    }


    /**
     * @param Application $app
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function signupAction(Application $app, Request $request)
    {
        $user = [];

        $form = $app['form.factory']->createBuilder(
            SignupType::class,
            $user
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = new UserRepository($app['db']);

            $user = $form->getData();
            $password = $user['password'];
            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($password, '');
            $user['role_id'] = 2;
            $userRepository->save($user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('posts_index_paginated'), 301);
        }


        return $app['twig']->render(
            'user/new.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function logoutAction(Application $app)
    {
        $app['session']->clear();

        return $app['twig']->render('auth/logout.html.twig', []);
    }
}