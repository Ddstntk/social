<?php
/**
 * Admin controller.
 *
 * @author    Konrad Szewczuk
 * @copyright (c) 2018 Konrad Szewczuk
 * @category  Social Media
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 *
 * Collage project - social network
 */
namespace Controller;

use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Repository\ChatRepository;
use Repository\FriendsRepository;
use Repository\PostsRepository;
use Repository\CommentsRepository;
use Form\MessageType;
use Form\ChatType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class AdminController.
 */
class AdminController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/users', [$this, 'manageUsers'])
            ->method('POST|GET')
            ->bind('admin_users');
        $controller->match('/posts', [$this, 'managePosts'])
            ->method('POST|GET')
            ->bind('admin_posts');
        $controller->match('/comments/{postId}', [$this, 'manageComments'])
            ->method('POST|GET')
            ->bind('admin_comments');
        $controller->match('/users/{id}/delete', [$this, 'deleteUsers'])
            ->method('POST|GET')
            ->bind('admin_users_delete');
        $controller->match('/posts/{id}/delete', [$this, 'deletePosts'])
            ->method('POST|GET')
            ->bind('admin_posts_delete');
        $controller->match('/comments/{postId}/delete', [$this, 'deleteComments'])
            ->method('POST|GET')
            ->bind('admin_comments_delete');
        return $controller;
    }

    public function manageUsers(Application $app)
    {
        $userRepository = new UserRepository($app['db']);
        return $app['twig']->render(
            'user/index_simple.html.twig',
            ['users' => $userRepository->findAll()]
        );
    }

    public function deleteUsers(Application $app, $id)
    {
        $userRepository = new UserRepository($app['db']);
        $friendsRepository = new FriendsRepository($app['db']);
        $userRepository->delete($id);
        return $app['twig']->render(
            'user/index_simple.html.twig',
            ['users' => $userRepository->findAll()]
        );
    }

    public function managePosts(Application $app)
    {
        $postRepository = new PostsRepository($app['db']);
        return $app['twig']->render(
            'posts/index_simple.html.twig',
            ['posts' => $postRepository->findAll()]
        );
    }

    public function deletePosts(Application $app, $id)
    {
        $postRepository = new PostsRepository($app['db']);
        $postRepository->delete($id);
        return $app['twig']->render(
            'posts/index_simple.html.twig',
            ['posts' => $postRepository->findAll()]
        );
    }


    public function manageComments(Application $app, $postId, $page = 1)
    {
        $commentsRepository = new CommentsRepository($app['db']);
        $postsRepository = new PostsRepository($app['db']);

        return $app['twig']->render(
            'comments/index_simple.html.twig',
            [
                'paginator' => $commentsRepository->findAllPaginated($page, $postId),
                ]
        );
    }

}