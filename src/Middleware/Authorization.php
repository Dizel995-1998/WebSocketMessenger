<?php

namespace Middleware;

use JsonException;
use Lib\Jwt\JwtToken;
use Lib\Middleware\BaseMiddleware;
use Lib\Request\Request;
use Lib\Response\BadRequest;
use Lib\Response\Forbidden;
use ReflectionException;

class Authorization extends BaseMiddleware
{
    /**
     * @param Request $request
     * @throws JsonException
     * @throws ReflectionException
     * @throws Forbidden
     */
    public function handle(Request $request)
    {
        if (!$jwtToken = $request->getHeaderLine('authorization')) {
            throw new Forbidden('Требуется авторизация');
        }

        JwtToken::parse($jwtToken);
    }
}