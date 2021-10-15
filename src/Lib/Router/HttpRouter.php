<?php

namespace Lib\Router;

use Lib\Response\JsonResponse;
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
            if ($route->getHttpMethod() != $this->request->getMethod()) {
                continue;
            }

            if (preg_match($route->getPatternUrl(), $this->request->getUri()->getPath(), $matches)) {
                $newMatches = [];
                foreach ($matches as $key => $value) {
                    if (is_int($key)) {
                        continue;
                    }

                    $newMatches[$key] = $value;
                }

                return $route->runController($this->request, $newMatches);
            }
        }

        return new JsonResponse(['code' => 'not found'], self::HTTP_CODE_NOT_FOUND);
    }
}