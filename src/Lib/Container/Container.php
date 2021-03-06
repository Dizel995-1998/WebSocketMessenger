<?php

namespace Lib\Container;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Container implements ContainerInterface
{
    private static array $services = [];

    /**
     * Устанавливает соответствие между интерфейсом сервиса и его конкретным экземпляром
     * @param string $resolveService
     * @param string|null $currentService
     * @param array|null $args
     */
    public static function setService(string $resolveService, array $args = null, string $currentService = null) : void
    {
        if (empty($resolveService)) {
            throw new \InvalidArgumentException('Resolve service cant be empty');
        }

        if (isset(self::$services[$resolveService])) {
            throw new \InvalidArgumentException(sprintf('Trying to overwrite service: %s', $resolveService));
        }

        self::$services[$resolveService] = [
            'current_service' => $currentService ?: $resolveService,
            'args' => $args
        ];
    }

    /**
     * Возвращает запрашиваемый обьект сервиса
     * @param string|callable $resolveService
     * @return mixed
     * @throws ReflectionException
     */
    public static function getService($resolveService)
    {
        if (!is_callable($resolveService) && !is_string($resolveService)) {
            throw new \InvalidArgumentException('Resolve service must be string or callable type');
        }

        if (empty($resolveService)) {
            throw new \InvalidArgumentException('Resolve service cant be empty');
        }

        if (is_callable($resolveService)) {
            return self::getServiceForCallable($resolveService);
        }

        $service = null;

        if (interface_exists($resolveService) && !$service = self::$services[$resolveService]['current_service']) {
            throw new RuntimeException(sprintf('Can\'t find resolve service for %s', $resolveService));
        }

        $reflection = new ReflectionClass($service ?: $resolveService);
        $arDependencies = [];

        if ($reflection->isInterface()) {
            throw new RuntimeException(sprintf('Service "%s" can\'t be interface', $reflection->getName()));
        }

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException('Can\'t instance object of service');
        }

        if ($constructor = $reflection->getConstructor()) {
            $serviceDependencies = $constructor->getParameters();

            foreach ($serviceDependencies as $dependency) {
                /** Если у зависимости есть дефолтное значение, взять его */
                if (!isset(self::$services[$resolveService]['args'][$dependency->getName()]) && $dependency->isDefaultValueAvailable()) {
                    $arDependencies[] = $dependency->getDefaultValue();
                    continue;
                }

                /*** Если зависимость есть класс, попытаться найти зависимости зависимостей */
                if ($dependency->getType() && !$dependency->getType()->isBuiltin()) {
                    $arDependencies[] = self::getService($dependency->getType()->getName());
                    continue;
                }

                /** Если зависимость не класс, а примитивный тип */
                if (!isset(self::$services[$resolveService]['args'][$dependency->getName()])) {
                    throw new
                        RuntimeException(
                            sprintf('Can\'t resolve primitive dependencies, arg "%s" have no value "%s" class',
                                $dependency->getName(),
                                $resolveService
                            )
                    );
                }

                $arDependencies[] = self::$services[$resolveService]['args'][$dependency->getName()];
            }
        }

        return $reflection->newInstanceArgs($arDependencies);
    }

    /**
     * Проверяет существует ли сервис
     * @param string $resolveService
     * @return bool
     */
    public static function hasService(string $resolveService) : bool
    {
        return isset(self::$services[$resolveService]);
    }

    /**
     * @param callable $func
     * @return mixed
     * @throws ReflectionException
     */
    private static function getServiceForCallable(callable $func)
    {
        $reflection = new \ReflectionFunction($func);
        $arDependencies = [];

        if ($dependencies = $reflection->getParameters()) {
            foreach ($dependencies as $dependency) {
                if ($dependency->isDefaultValueAvailable()) {
                    $arDependencies[] = $dependency->getDefaultValue();
                    continue;
                }

                if ($dependencyClass = $dependency->getClass()) {
                    $arDependencies[] = self::getService($dependencyClass->getName());
                }
            }
        }

        return $reflection->invokeArgs($arDependencies);
    }

    /**
     * fixme: избавиться от deprecated методов
     * TODO добавить в интерфейс
     * Резолвит зависимости метода сервиса
     * @param object $service
     * @param string $methodName
     * @return mixed возвращает результат метода сервиса
     * @throws ReflectionException
     */
    public static function resolveMethodDependencies(object $service, string $methodName, array $additionalArgs = [])
    {
        if (!method_exists($service, $methodName)) {
            throw new \InvalidArgumentException(sprintf('Service: %s dont have %s method', get_class($service), $methodName));
        }

        if (!$methodName) {
            throw new \InvalidArgumentException('Method name can\'t be empty');
        }

        $reflectionMethod = new \ReflectionMethod($service, $methodName);
        $methodArgs = $reflectionMethod->getParameters();
        $arDependencies = [];

        foreach ($methodArgs as $arg) {
            if ($arg->isDefaultValueAvailable()) {
                $arDependencies[] = $arg->getDefaultValue();
                continue;
            }

            if ($dependencyClass = $arg->getClass()) {
                $arDependencies[] = self::getService($dependencyClass->getName());
                continue;
            }

            // примитивные типы не имеющие дефолтных значений
            if ($value = $additionalArgs[$arg->getName()]) {
                $arDependencies[] = $value;
            }
        }

        return $reflectionMethod->invokeArgs($service, $arDependencies);
    }
}