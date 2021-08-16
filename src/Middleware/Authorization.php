<?php

namespace Middleware;

use Lib\Jwt\JwtToken;
use Lib\Middleware\BaseMiddleware;
use Lib\Request\Request;
use Lib\Response\ValidationError;

class Authorization extends BaseMiddleware
{
    /**
     * @throws ValidationError
     * @throws \ReflectionException
     * @throws \JsonException
     */
    public function handle(Request $request)
    {
        if (!$jwtToken = $request->getHeaderLine('authorization')) {
            // todo сменить тип exception на свой, хардкод названия заголовка
            throw new ValidationError('Отсутствует заголовок: authorization');
        }

        JwtToken::parse($jwtToken);
    }
}