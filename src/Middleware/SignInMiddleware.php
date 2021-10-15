<?php

namespace Middleware;

/**
 * Проверка наличия логина и пароля
 */
class SignInMiddleware extends UserMiddleware
{
    function getValidationRules(): array
    {
        return [
            'login' => self::LOGIN_RULE,
            'password' => self::PASSWORD_RULE
        ];
    }
}