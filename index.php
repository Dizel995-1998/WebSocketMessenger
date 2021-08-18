<?php

use Lib\Container\Container;
use Lib\Database\QueryBuilderUpdater;

require_once 'vendor/autoload.php';

$httpRouter = new \Lib\Router\HttpRouter(Container::getService(Lib\Request\Request::class));
$routeCollection = new \Lib\RouteCollection\RouteCollection();
$routeCollection->addRoutes(require_once 'src/Routes/v1/routes.php');
$response = $httpRouter->run($routeCollection);

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $headerName => $headerValue) {
    header($headerName . ': ' . current($headerValue));
}

echo $response->getBody();