<?php

namespace Middleware;

class CreateUser extends BaseMiddleware
{
    function getValidationRules(): array
    {
        return [
            'name' => 'required|alpha_spaces|min:4',
            'status' => 'alpha_spaces'
        ];
    }
}