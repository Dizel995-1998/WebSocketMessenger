<?php

namespace Lib\Route;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface IRoute
{
    public function getPatternUrl() : string;

    public function getHttpMethod() : string;

    public function runController(RequestInterface $request, array $matches = []) : ResponseInterface;
}