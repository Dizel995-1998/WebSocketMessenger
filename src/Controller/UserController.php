<?php

namespace Controller;

use Entity\AccessToken;
use Entity\User;
use GuzzleHttp\Psr7\Response;
use Lib\Database\EntityManager\EntityManager;
use Lib\Jwt\JwtToken;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\JsonResponse;
use Lib\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Service\GoogleAuth\GoogleAuth;

class UserController
{
    /**
     * @throws BadRequest|\ReflectionException
     */
    public function authorizeMe(Request $request, EntityManager $entityManager, JwtToken $jwtToken) : ResponseInterface
    {
        $user = $entityManager->findBy(User::class, [
            'login' => $request->get('login'),
            'password' => $request->get('password')
        ]);

        if (!$user) {
            throw new BadRequest('Incorrect login or password');
        }

        $token = (string) ($jwtToken->setUserId($user->getId()));

        // Вносим токен в табличку активных токенов
        $accessToken = $entityManager->findBy(AccessToken::class, ['userId' => $user->getId()]) ?? new AccessToken();
        $accessToken
            ->setToken($token)
            ->setUserId($user->getId());

        $entityManager->save($accessToken);

        // todo: Это ещё не полноценный JWT, нет refresh токена
        return new JsonResponse(['access_token' => $token]);
    }

    public function me(Request $request, EntityManager $entityManager, $id): ResponseInterface
    {
        return new SuccessResponse($entityManager->findByPrimaryKey(User::class, $id));
    }

    public function link(GoogleAuth $googleAuth)
    {
        return new Response(200, [], $googleAuth->formAuthLink());
    }

    public function updatePassword(Request $request)
    {

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

    /**
     * @param Request $request
     * @param EntityManager $entityManager
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    public function create(Request $request, EntityManager $entityManager) : ResponseInterface
    {
        $user = (new User())
            ->setName($request->get('name'))
            ->setStatus($request->get('status'))
            ->setPassword($request->get('password'))
            ->setPictureUrl('');

        return new JsonResponse($entityManager->save($user));
    }

    public function test(EntityManager $entityManager)
    {
        $user = (new User())->setName('Шамиль');
        $entityManager->save($user);

        return new JsonResponse($user);
    }
}