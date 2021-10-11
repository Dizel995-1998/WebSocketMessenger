<?php

use Lib\Container\Container;
use Lib\Database\QueryBuilderUpdater;

require_once 'vendor/autoload.php';

//$httpRouter = new \Lib\Router\HttpRouter(Container::getService(Lib\Request\Request::class));
//$routeCollection = new \Lib\RouteCollection\RouteCollection();
//$routeCollection->addRoutes(require_once 'src/Routes/v1/routes.php');
//$response = $httpRouter->run($routeCollection);
//
//http_response_code($response->getStatusCode());
//
//foreach ($response->getHeaders() as $headerName => $headerValue) {
//    header($headerName . ': ' . current($headerValue));
//}
//
//echo $response->getBody();


if (($pidChildProccess = pcntl_fork()) == -1) {
    throw new RuntimeException('Cannot fork proccess');
}

if ($pidChildProccess) {
    echo 'PID child proccess: ' . $pidChildProccess;
    exit();
}

// от child процесса создаём ещё один процесс

if (($pidChildProccess = pcntl_fork()) == -1) {
    throw new RuntimeException('Cannot fork proccess');
}

$port = 45500;
$clients = array();
$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
$z = socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
$y = socket_set_option($socket, SOL_SOCKET, SO_REUSEPORT, 1);

$reusePort = socket_get_option($socket, SOL_SOCKET, SO_REUSEPORT);

$address = '0.0.0.0';

if (!socket_bind($socket, $address, $port)) {
    throw new RuntimeException('Cannot bind socket: ' . socket_strerror(socket_last_error($socket)));
}

socket_listen($socket);
socket_set_nonblock($socket);

while(true)
{
    if(($newc = socket_accept($socket)) !== false)
    {
        echo "Client $newc has connected\n";
        $clients[] = $newc;
    }
}