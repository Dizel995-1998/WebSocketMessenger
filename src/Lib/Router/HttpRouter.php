<?php

namespace Lib\Router;

use Lib\Response\JsonResponse;
use Lib\Response\ValidationError;
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

            try {
                if (preg_match($route->getPatternUrl(), $this->request->getUri()->getPath())) {
                    return $route->runController($this->request);
                }
            } catch (\Throwable $e) {
                // todo хардкод
                return new JsonResponse(500, $e->getMessage());
            }
        }

        return new JsonResponse(self::HTTP_CODE_NOT_FOUND, ['code' => 'not found']);
    }
}