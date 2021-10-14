<?php

use Lib\Route\Route;
use Middleware\Authorization;
use Controller\UserController;
use Controller\AuthController;

return [
    new Route('/user/{id}/me', 'GET', UserController::class, 'me')
];
