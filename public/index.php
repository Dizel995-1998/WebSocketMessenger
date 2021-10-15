<?php

/**
 * Точка входа в приложение
 */

error_reporting(E_ERROR | E_PARSE);
ini_set('xdebug.var_display_max_depth', 10);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Lib\Container\Container;
use Lib\Request\Request;
use Lib\RouteCollection\RouteCollection;
use Lib\Router\HttpRouter;

$httpRouter = new HttpRouter(Container::getService(Request::class));
$routeCollection = (new RouteCollection())->addRoutes(require_once $_SERVER['DOCUMENT_ROOT'] . '/src/routes.php');

$response = $httpRouter->run($routeCollection);

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $headerName => $headerValue) {
    header($headerName . ': ' . current($headerValue));
}

echo $response->getBody();