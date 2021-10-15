<?php

namespace Middleware;

class CreateUserMiddleware extends UserMiddleware
{
    function getValidationRules(): array
    {
        return [
            'name' => self::NAME_RULE,
            'password' => self::PASSWORD_RULE,
            'status' => self::STATUS_RULE
        ];
    }
}