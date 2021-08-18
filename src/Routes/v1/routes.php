<?php

use Lib\Route\Route;
use Middleware\Authorization;
use Controller\UserController;
use Controller\AuthController;

return [
    new Route('/auth', 'POST', AuthController::class, 'getJwtToken'),

    new Route('/user', 'POST', AuthController::class, 'createUser'),

    (new Route('/user/me', 'GET', UserController::class, 'me'))
        ->addMiddleware(Authorization::class),

    (new Route('/user/{id}', 'GET', UserController::class, 'profile'))
        ->addMiddleware(Authorization::class)
];