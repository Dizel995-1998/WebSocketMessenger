<?php

namespace Lib\Middleware;

use Psr\Http\Message\RequestInterface;

interface IMiddleware
{
    public function setNext(IMiddleware $nextMiddleware);

    public function handle(RequestInterface $request);
}