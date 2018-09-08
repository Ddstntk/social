<?php
/**
 * Posts controller.
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
use Repository\PostsRepository;
use Form\PostType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class PostsController.
 */
class PostsController implements ControllerProviderInterface
{

    /**
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->assert('page', '[1-9]\d*')
            ->value('page', 1)
            ->bind('posts_index_paginated');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('posts_add');

        return $controller;
    }


    /**
     * @param Application $app
     * @param int         $page
     * @return mixed
     */
    public function indexAction(Application $app, $page = 1)
    {
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();

        $postsRepository = new PostsRepository($app['db']);

        $post = [];

        $form = $app['form.factory']->createBuilder(
            PostType::class,
            $post
        )->getForm();

        return $app['twig']->render(
            'posts/index.html.twig',
            ['paginator' => $postsRepository->findAllPaginated($page, $userId),
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * .
     *
     * @param  Application $app
     * @param  Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addAction(Application $app, Request $request)
    {
        $post = [];
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();

        $form = $app['form.factory']->createBuilder(
            PostType::class,
            $post
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postsRepository = new PostsRepository($app['db']);
            $postsRepository->save($form->getData(), $userId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('posts_index_paginated'), 301);
        }
    }
}