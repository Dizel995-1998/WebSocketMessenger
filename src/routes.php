<?php

use Lib\Route\Route;
use Controller\UserController;
use Middleware\ChangeUserPasswordMiddleware;
use Middleware\CreateUserMiddleware;
use Middleware\UpdateUserMiddleware;

return [
    new Route('/user/{id}/me', 'GET', UserController::class, 'me'),
    new Route('/auth/login', 'GET', UserController::class, 'login'),
    new Route('/auth/link', 'GET', UserController::class, 'link'),
    new Route('/test', 'GET', UserController::class, 'test'),


    /** Пользователь */
    (new Route('/user', 'POST', UserController::class, 'create'))
        ->addMiddleware(CreateUserMiddleware::class),

    (new Route('/user', 'PUT', UserController::class, 'update'))
        ->addMiddleware(UpdateUserMiddleware::class),

    (new Route('/user/password', 'PUT', UserController::class, 'updatePassword'))
        ->addMiddleware(ChangeUserPasswordMiddleware::class)
];
