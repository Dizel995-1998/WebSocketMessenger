<?php

namespace Middleware;

class UpdateUserMiddleware extends UserMiddleware
{
    function getValidationRules(): array
    {
        return [
            'name' => self::NAME_RULE,
            'status' => self::STATUS_RULE,
            'image' => ''
        ];
    }
}