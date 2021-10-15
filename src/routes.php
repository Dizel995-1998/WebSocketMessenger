<?php

use Lib\Route\Route;
use Controller\UserController;
use Middleware\AuthorizeMiddleware;
use Middleware\ChangeUserPasswordMiddleware;
use Middleware\CreateUserMiddleware;
use Middleware\SignInMiddleware;
use Middleware\UpdateUserMiddleware;

return [
    /** Пользователь */
    (new Route('/users', 'POST', UserController::class, 'create'))
        ->addMiddleware(CreateUserMiddleware::class),

    (new Route('/user/auth', 'POST', UserController::class, 'authorizeMe'))
        ->addMiddleware(SignInMiddleware::class),

    (new Route('/user/password', 'PUT', UserController::class, 'updatePassword'))
        ->addMiddleware(ChangeUserPasswordMiddleware::class),

    // тестовый ендпоинт закрытый авторизацией
    (new Route('/user/test', 'GET', UserController::class, 'test'))
        ->addMiddleware(AuthorizeMiddleware::class),
];
