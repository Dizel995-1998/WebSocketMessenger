<?php

namespace Lib\Middleware;

use Psr\Http\Message\RequestInterface;

abstract class BaseMiddleware implements IMiddleware
{
    private ?IMiddleware $nextMiddleware = null;

    public function setNext(IMiddleware $nextMiddleware)
    {
        $this->nextMiddleware = $nextMiddleware;
    }

    public function handle(RequestInterface $request)
    {
        if ($this->nextMiddleware) {
            $this->nextMiddleware->handle($request);
        }
    }
}