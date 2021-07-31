<?php

namespace Lib\Router;

use Lib\RouteCollection\RouteCollection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpRouter
{
    const HTTP_CODE_NOT_FOUND = 404;

    private RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param RouteCollection $routeCollection
     * @return ResponseInterface
     */
    public function run(RouteCollection $routeCollection) : ResponseInterface
    {
        foreach ($routeCollection as $route) {
            if (preg_match($route->getPatternUrl(), $this->request->getUri()->getPath())) {
                return $route->runController($this->request);
            }
        }

        return new \GuzzleHttp\Psr7\Response(self::HTTP_CODE_NOT_FOUND, [], 'Page not found');
    }
}