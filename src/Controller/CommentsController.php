<?php
/**
 * Comments controller.
 *
 * @copyright (c) 2018 Konrad Szewczuk
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
use Repository\CommentsRepository;
use Repository\PostsRepository;
use Form\CommentType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class PostsController.
 */
class CommentsController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/post/{postId}', [$this, 'indexAction'])
            ->assert('page', '[1-9]\d*')
            ->value('page', 1)
            ->bind('comments_index_paginated');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('comments_add');
        return $controller;
    }

    /**
     * @param Application $app
     * @param $postId
     * @param int         $page
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Application $app, $postId, $page = 1)
    {
        $commentsRepository = new CommentsRepository($app['db']);
        $postsRepository = new PostsRepository($app['db']);

        $post = [];

        $form = $app['form.factory']->createBuilder(
            CommentType::class,
            $post
        )->getForm();

        return $app['twig']->render(
            'comments/index.html.twig',
            [
                'paginator' => $commentsRepository->findAllPaginated($page, $postId),
                'xd' => $postsRepository->findOneById($postId),
                'post' => $post,
                'form' => $form->createView()]
        );
    }


    /**
     * @param Application $app
     * @param int         $page
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addAction(Application $app, $page = 1, Request $request)
    {
        $post = [];
        $form = $app['form.factory']->createBuilder(
            CommentType::class,
            $post
        )->getForm();
        $form->handleRequest($request);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        $x = $request->headers->get('referer');
        var_dump($x);

        if(preg_match("/\/(\d+)$/", $x, $matches)) {
            $id=$matches[1];
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $postsRepository = new CommentsRepository($app['db']);
            $postsRepository->save($form->getData(), $id, $userId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('comments_index_paginated', array("postId"=> $id)), 301);
        }
    }

}