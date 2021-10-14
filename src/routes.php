<?php

use Lib\Route\Route;
use Controller\UserController;

return [
    new Route('/user/{id}/me', 'GET', UserController::class, 'me'),
    new Route('/auth/login', 'GET', UserController::class, 'login'),
    new Route('/auth/link', 'GET', UserController::class, 'link'),
    new Route('/test', 'GET', UserController::class, 'test')
];
