<?php

namespace Middleware;

use Entity\AccessToken;
use Lib\Database\EntityManager\EntityManager;
use Lib\Jwt\JwtToken;
use Lib\Middleware\IMiddleware;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\Forbidden;

class AuthorizeMiddleware implements IMiddleware
{
    public function __construct(protected EntityManager $entityManager) {}

    /**
     * @throws \ReflectionException
     * @throws Forbidden
     * @throws \JsonException
     */
    public function handle(Request $request)
    {
        if (!$bearerToken = $request->getBearerToken()) {
            throw new Forbidden('You have must have Bearer token');
        }

        try {
            $jwtToken = JwtToken::parse($bearerToken);
            $accessToken = $this->entityManager->findBy(AccessToken::class, [
                'userId' => $jwtToken->getUserId(),
                'token' => $bearerToken
            ]);

            if (!$accessToken) {
                throw new Forbidden('Access token already died');
            }
        } catch (\Throwable $e) {
            // fixme: принести систему логирования
            throw new BadRequest('Invalid access token token');
        }
    }
}