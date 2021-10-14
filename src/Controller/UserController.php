<?php

namespace Controller;

use Entity\User;
use Lib\Database\EntityManager\EntityManager;
use Lib\Request\Request;
use Lib\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    public function me(Request $request, EntityManager $entityManager, $id): ResponseInterface
    {
        return new SuccessResponse($entityManager->findByPrimaryKey(User::class, $id));
    }
}