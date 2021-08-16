<?php

namespace Lib\Middleware;

use Lib\Request\Request;
use Rakit\Validation\Validator;

abstract class BaseMiddleware implements IMiddleware
{
    protected Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    abstract public function handle(Request $request);
}