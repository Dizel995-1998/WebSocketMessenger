<?php

namespace Lib\Route;

use Exception;
use InvalidArgumentException;
use Lib\Container\Container;
use Lib\Middleware\IMiddleware;
use Lib\Response\JsonResponse;
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

    /** @var string  */
    private string $controllerAction;

    /**
     * @param string|callable $controller - контроллер
     * @param string $patternUrl - URL паттерн согласно POSIX regex
     * @param string $method - HTTP метод на который заточен данный роут
     */
    public function __construct(string $patternUrl, string $method, $controller, string $action = '')
    {
        if (!is_string($controller) && !is_callable($controller)) {
            throw new InvalidArgumentException('Controller must be string or callable types');
        }

        if ($action && !is_string($controller)) {
            throw new InvalidArgumentException('Action arg must set only if you use controller class');
        }

        $method = strtoupper($method);

        if (!in_array($method, self::ALLOW_HTTP_VERBS)) {
            throw new InvalidArgumentException(sprintf('Invalid http verb: %s, must be one of: %s', $method, implode(', ', self::ALLOW_HTTP_VERBS)));
        }

        $this->patternUrl = $patternUrl;
        $this->method = $method;
        $this->controller = $controller;
        $this->controllerAction = $action;
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
    public function getHttpMethod(): string
    {
        return $this->method;
    }

    /**
     * Запускает контроллер роута
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \ReflectionException
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

        try {
            return is_string($this->controller) ? $this->runStringController($this->controller, $this->controllerAction) : $this->runCallableController($this->controller);
        } catch (\Throwable $e) {
            return new JsonResponse(500, [], ['error' => true, 'code' => unserialize($e->getMessage()) ?: $e->getMessage()]);
        }
    }

    /**
     * Запуск контроллера оформленного в виде класса
     * @param string $controllerName
     * @param string $controllerAction
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    private function runStringController(string $controllerName, string $controllerAction) : ResponseInterface
    {
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
     * @throws \ReflectionException
     */
    private function runCallableController(callable $controller) : ResponseInterface
    {
        return Container::getService($controller);
    }

    public function addMiddleware(IMiddleware $middleware) : self
    {
        // TODO оформить в виде вызова сервис провайдера
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