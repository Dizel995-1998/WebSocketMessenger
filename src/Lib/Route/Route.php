<?php

namespace Lib\Route;

use Exception;
use InvalidArgumentException;
use Lib\Container\Container;
use Lib\Middleware\IMiddleware;
use Lib\Response\BadRequest;
use Lib\Response\HttpErrorException;
use Lib\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;
use Throwable;

class Route implements IRoute
{
    const ALLOW_HTTP_VERBS = [
        'GET',
        'POST',
        'PATCH',
        'DELETE',
        'PUT'
    ];

    /*** @var string[] array */
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


    protected function preparePattern(string $patternUrl) : ?string
    {
        return preg_replace_callback('~{(?<param_name>\S+)}~', function ($item) {
            return sprintf('(?<%s>\d+)', $item['param_name']);
        }, $patternUrl);
    }

    /**
     * Возвращает паттерн роута
     * @return string
     */
    public function getPatternUrl(): string
    {
        return '~^' . $this->preparePattern($this->patternUrl) . '$~';
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
     * @param array $matches
     * @return ResponseInterface
     */
    public function runController(RequestInterface $request, array $matches = []): ResponseInterface
    {
        if ($this->arMiddlewares) {
            try {
                foreach ($this->arMiddlewares as $middleware) {
                    Container::resolveMethodDependencies(Container::getService($middleware), 'handle');
                }
            } catch (Exception $e) {
                // todo дубль кода, должно быть инкапсулировано в классы response
                return new JsonResponse(['error' => true, 'code' => unserialize($e->getMessage()) ?: $e->getMessage()], 422);
            }
        }

        try {
            return is_string($this->controller) ? $this->runStringController($this->controller, $this->controllerAction, $matches) : $this->runCallableController($this->controller, $matches);
        } catch (HttpErrorException $e) {
            return new JsonResponse(['error' => true, 'code' => unserialize($e->getMessage()) ?: $e->getMessage()], $e->getHttpErrorCode());
        } catch (Throwable $e) {
            // какая то внутренняя ошибка, или ошибка из сторонних пакетов
            return new JsonResponse(['error' => true, 'code' => $e->getMessage()], 400);
        }
    }

    /**
     * Запуск контроллера оформленного в виде класса
     * @param string $controllerName
     * @param string $controllerAction
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    private function runStringController(string $controllerName, string $controllerAction, array $matches = []) : ResponseInterface
    {
        if (!class_exists($controllerName)) {
            throw new InvalidArgumentException(sprintf('Controller: %s not found', $controllerName));
        }

        if (!method_exists($controllerName, $controllerAction)) {
            throw new InvalidArgumentException(sprintf('Controller "%s" dont have "%s" action', $controllerName, $controllerAction));
        }

        return Container::resolveMethodDependencies(Container::getService($controllerName), $controllerAction, $matches);
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

    public function addMiddleware(string $middleware) : self
    {
        if (array_flip(get_class_methods($middleware))['handle'] === null) {
            throw new InvalidArgumentException(sprintf('Посредник %s не имеет метода обработчика', $middleware));
        }

        $this->arMiddlewares[] = $middleware;
        return $this;
    }
}