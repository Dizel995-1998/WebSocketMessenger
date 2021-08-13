<?php

namespace Lib\Route;

use Exception;
use InvalidArgumentException;
use Lib\Container\Container;
use Lib\Middleware\IMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;

class Route implements IRoute
{
    const ALLOW_HTTP_VERBS = [
        'GET',
        'POST',
        'PATCH',
        'DELETE',
        'PUT'
    ];

    /*** @var IMiddleware[] array */
    private array $arMiddlewares = [];

    /*** @var string|callable */
    private $controller;

    /*** @var string */
    private string $patternUrl;

    /*** @var string  */
    private string $method;

    /**
     * @param string|callable $controller - контроллер
     * @param string $patternUrl - URL паттерн согласно POSIX regex
     * @param string $method - HTTP метод на который заточен данный роут
     */
    public function __construct(string $patternUrl, string $method, $controller)
    {
        if (!is_string($controller) && !is_callable($controller)) {
            throw new InvalidArgumentException('Controller must be string or callable types');
        }

        $method = strtoupper($method);

        if (!in_array($method, self::ALLOW_HTTP_VERBS)) {
            throw new InvalidArgumentException(sprintf('Invalid http verb: %s, must be one of: %s', $method, implode(', ', self::ALLOW_HTTP_VERBS)));
        }

        $this->patternUrl = $patternUrl;
        $this->method = $method;
        $this->controller = $controller;
    }

    /**
     * Возвращает паттерн роута
     * @return string
     */
    public function getPatternUrl(): string
    {
        return '~' . $this->patternUrl . '~';
    }

    /**
     * Возвращает HTTP метод роута
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->method;
    }

    /**
     * Запускает контроллер роута
     * @return ResponseInterface
     */
    public function runController(RequestInterface $request): ResponseInterface
    {
        if ($this->arMiddlewares) {
            try {
                reset($this->arMiddlewares)->handle($request);
            } catch (Exception $e) {
                return new Response(200, [], $e->getMessage());
            }
        }

        return is_string($this->controller) ? $this->runStringController($this->controller) : $this->runCallableController($this->controller);
    }

    /**
     * Запуск контроллера оформленного в виде класса
     * @param string $controller
     * @return ResponseInterface
     */
    private function runStringController(string $controller) : ResponseInterface
    {
        $arController = explode(':', $controller);

        if (count($arController) != 2) {
            throw new InvalidArgumentException(sprintf('Controller must be like "ControllerName:ControllerAction", given %s', $controller));
        }

        $controllerName = 'Controller\\' . $arController[0];
        $controllerAction = $arController[1];

        if (!class_exists($controllerName)) {
            throw new InvalidArgumentException(sprintf('Controller: %s not found', $controllerName));
        }

        if (!method_exists($controllerName, $controllerAction)) {
            throw new InvalidArgumentException(sprintf('Controller "%s" dont have "%s" action', $controllerName, $controllerAction));
        }

        return Container::resolveMethodDependencies(Container::getService($controllerName), $controllerAction);
    }

    /**
     * Вызов контроллера оформленного в виде callable функции
     * @param callable $controller
     * @return ResponseInterface
     */
    private function runCallableController(callable $controller) : ResponseInterface
    {
        // тут вызов DI контейнера
        return $controller();
    }

    public function addMiddleware(IMiddleware $middleware) : self
    {
        $currentMiddleware = current($this->arMiddlewares);
        if ($currentMiddleware == null) {
            $this->arMiddlewares[] = $middleware;
            return $this;
        }

        $currentMiddleware->setNext($middleware);
        $this->arMiddlewares[] = $middleware;
        return $this;
    }
}