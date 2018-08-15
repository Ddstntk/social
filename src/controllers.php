<?php
/**
 * Routing and controllers.
 *
 * @copyright (c) 2016 Tomasz Chojna
 *
 * @link      http://epi.chojna.info.pl
 */


use Controller\UserController;


$app->mount('/user', new UserController());