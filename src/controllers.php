<?php
/**
 * Routing and controllers.
 *
 * @copyright (c) 2018 Konrad Szewczuk
 */


use Controller\UserController;
use Controller\PostsController;
use Controller\AuthController;
use Controller\ChatController;
use Controller\FriendsController;
use Controller\CommentsController;
use Controller\PhotosController;
use Controller\AdminController;

$app->mount('/user', new UserController());
$app->mount('/posts', new PostsController());
$app->mount('/auth', new AuthController());
$app->mount('/chat', new ChatController());
$app->mount('/friend', new FriendsController());
$app->mount('/comment', new CommentsController());
$app->mount('/photo', new PhotosController());
$app->mount('/admin', new AdminController());