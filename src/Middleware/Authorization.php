<?php

namespace Middleware;

use Lib\Jwt\JwtToken;
use Lib\Middleware\BaseMiddleware;
use Lib\Request\Request;
use Lib\Response\BadRequest;

class Authorization extends BaseMiddleware
{
    /**
     * @param Request $request
     * @throws BadRequest
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function handle(Request $request)
    {
        if (!$jwtToken = $request->getHeaderLine('authorization')) {
            // todo сменить тип exception на свой, хардкод названия заголовка
            throw new BadRequest('Отсутствует заголовок: authorization');
        }

        JwtToken::parse($jwtToken);
    }
}