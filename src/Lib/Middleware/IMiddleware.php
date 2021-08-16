<?php

namespace Lib\Middleware;

use Lib\Request\Request;

interface IMiddleware
{
    public function handle(Request $request);
}