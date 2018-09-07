<?php
/**
 * Chat controller.
 *
 * @copyright (c) 2018 Konrad Szewczuk
 */
namespace Controller;

use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Repository\ChatRepository;
use Repository\FriendsRepository;
use Form\MessageType;
use Form\ChatType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class PostsController.
 */
class ChatController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/view/{id}', [$this, 'indexAction'])->bind('chat_index_paginated');
        $controller->get('/all', [$this, 'indexChats'])->bind('chat_index');
        $controller->match('/send/{id}', [$this, 'sendAction'])
            ->method('POST|GET')
            ->bind('messages_send');
        $controller->match('/new', [$this, 'newChat'])
            ->method('POST|GET')
            ->bind('conversation_new');
        return $controller;
    }

    /**
     * @param Application $app
     * @param $userId
     * @throws \Doctrine\DBAL\DBALException
     */

    public function newChat(Application $app, Request $request)
    {
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();

        $conversation = [];

        $friendsRepository = new friendsRepository($app['db']);

        $friends = $friendsRepository -> friendsNames();
        //        var_dump($friends);

        foreach($friends as $k) {
            $fullname = $k['name'].' '.$k['surname'];
            $friendList[$fullname] = $k['PK_idUsers'];
        }

        var_dump($friendList);
        $form = $app['form.factory']->createBuilder(
            ChatType::class,
            $conversation,
            array(
                'data' => $friendList
                )
        )->getForm();
        $form->handleRequest($request);

        $id = 2;

        if ($form->isSubmitted()) {
            $chatRepository = new ChatRepository($app['db']);
            $chatRepository->addChat($form->getData(), $id);

            $app['session']->getFlashBag()->add(
                'conversations',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );
        }
        return $app['twig']->render(
            'chat/new.html.twig',
            [
            //                'conversation' => $conversation,
                'form' => $form->createView(),
            ]
        );
    }
    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return string Response
     */
    public function indexAction(Application $app, $page = 1, $id)
    {
        $chatRepository = new ChatRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        return $app['twig']->render(
            'chat/index.html.twig',
            ['paginator' => $chatRepository->findAllPaginated($page, $userId, $id),
                'user' => $userId]
        );
    }

    /**
     * @param Application $app
     * @return mixed
     */

    public function indexChats(Application $app)
    {
        $chatRepository = new ChatRepository($app['db']);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        return $app['twig']->render(
            'chat/all.html.twig',
            ['chats' => $chatRepository->findAllChats($userId),
                'user' => $userId]
        );
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param $id
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function sendAction(Application $app, Request $request, $id)
    {
        $post = [];

        $form = $app['form.factory']->createBuilder(
            MessageType::class,
            $post
        )->getForm();
        $form->handleRequest($request);
        $userId = $app['security.token_storage']->getToken()->getUser()->getID();
        if ($form->isSubmitted() && $form->isValid()) {
            $postsRepository = new ChatRepository($app['db']);
            $postsRepository->save($form->getData(), $userId, $id);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );
        }


        return $app['twig']->render(
            'chat/send.html.twig',
            [
                'id' => $id,
                'post' => $post,
                'form' => $form->createView(),
            ]
        );
    }


}