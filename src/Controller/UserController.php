<?php

namespace Controller;

use Entity\User;
use GuzzleHttp\Psr7\Response;
use Lib\Database\EntityManager\EntityManager;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\JsonResponse;
use Lib\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Service\GoogleAuth\GoogleAuth;

class UserController
{
    public function me(Request $request, EntityManager $entityManager, $id): ResponseInterface
    {
        return new SuccessResponse($entityManager->findByPrimaryKey(User::class, $id));
    }

    public function link(GoogleAuth $googleAuth)
    {
        return new Response(200, [], $googleAuth->formAuthLink());
    }

    public function login(Request $request, GoogleAuth $googleAuth, EntityManager $entityManager)
    {
        if (!$code = $request->get('code')) {
            return new BadRequest('There is no "code" param');
        }

        $accessToken = $googleAuth->getAccessToken($code);

        $user = $entityManager->findBy(User::class, 'externalId', $accessToken->getId());

        if (!$user) {
            $user = (new User())
                ->setName($accessToken->getName())
                ->setPictureUrl($accessToken->getPictureUrl())
                ->setExternalId($accessToken->getId());

            $entityManager->save($user);
        }

        return new JsonResponse($user);
    }

    public function test(EntityManager $entityManager)
    {
        return new JsonResponse($entityManager->findBy(User::class, 'externalId', 1));
    }
}