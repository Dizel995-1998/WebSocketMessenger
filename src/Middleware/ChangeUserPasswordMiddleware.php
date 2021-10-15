<?php

namespace Middleware;

class ChangeUserPasswordMiddleware extends UserMiddleware
{
    function getValidationRules(): array
    {
        return [
            'current_password' => self::PASSWORD_RULE,
            'new_password' => self::PASSWORD_RULE
        ];
    }
}