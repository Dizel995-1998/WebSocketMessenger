<?php

namespace Lib\RouteCollection;

use InvalidArgumentException;
use Iterator;
use Lib\Route\IRoute;

class RouteCollection implements Iterator
{
    /**
     * @var IRoute[]
     */
    private array $arRoutes = [];

    /**
     * Добавляет роут в коллекцию
     * @param IRoute $route
     */
    public function addRoute(IRoute $route)
    {
        $this->arRoutes[] = $route;
    }

    /**
     * Предыдущие значения добавленные через addRoute будут обнулены
     * @param IRoute[] $routes
     */
    public function addRoutes(array $routes) : self
    {
        foreach ($routes as $route) {
            if (!$route instanceof IRoute) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Incoming array must consist only objects which implements "RouteInterface", %s given',
                        gettype($route)
                    )
                );
            }
        }

        $this->arRoutes = $routes;
        return $this;
    }

    public function current()
    {
        return current($this->arRoutes);
    }

    public function next()
    {
        return next($this->arRoutes);
    }

    public function key() : int
    {
        return key($this->arRoutes);
    }

    public function valid()
    {
        return (bool) $this->current();
    }

    public function rewind()
    {
        return null;
    }
}