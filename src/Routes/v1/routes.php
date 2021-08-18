<?php

use Lib\Route\Route;
use Controller\AuthController;

return [
    new Route('/auth', 'POST', AuthController::class, 'getJwtToken'),

    new Route('/user', 'POST', AuthController::class, 'createUser'),

    (new Route('/user/me', 'GET', \Controller\UserController::class, 'me'))
        ->addMiddleware(\Middleware\Authorization::class),

    (new Route('/user/{id}', 'GET', \Controller\UserController::class, 'profile'))
        ->addMiddleware(\Middleware\Authorization::class)
];