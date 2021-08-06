<?php

namespace Lib\Container;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Container implements ContainerInterface
{
    private static array $services = [];

    public static function setService(string $resolveService, string $currentService = null, array $args = null) : void
    {
        if (isset(self::$services[$resolveService])) {
            throw new \InvalidArgumentException(sprintf('Trying to overwrite service: %s', $resolveService));
        }

        self::$services[$resolveService] = [
            'current_service' => $currentService ?: $resolveService,
            'args' => $args
        ];
    }

    /**
     * @throws ReflectionException
     */
    public static function getService(string $resolveService) : object
    {
        if (!$service = self::$services[$resolveService]['current_service']) {
            throw new RuntimeException(sprintf('Can\'t resolve service: %s', $resolveService));
        }

        $reflection = new ReflectionClass($service);
        $arDependencies = [];

        if ($reflection->isInterface()) {
            throw new RuntimeException(sprintf('Service "%s" can\'t be interface', $service));
        }

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException('Can\'t instance object of service');
        }

        if ($constructor = $reflection->getConstructor()) {
            $serviceDependencies = $constructor->getParameters();

            foreach ($serviceDependencies as $dependency) {
                /** Если у зависимости есть дефолтное значение, взять его */
                if ($dependency->isDefaultValueAvailable()) {
                    $arDependencies[] = $dependency->getDefaultValue();
                    continue;
                }

                /** Если зависимость не класс, а примитивный тип */
                if (!$dependency->getClass()) {
                    if (!$primitiveTypeValue = self::$services[$resolveService]['args'][$dependency->getName()]) {
                        throw new RuntimeException('Can\'t resolve primitive dependencies');
                    }

                    $arDependencies[] = $primitiveTypeValue;
                    continue;
                }

                $arDependencies[] = self::getService($dependency->getClass()->getName());
            }
        }

        return $reflection->newInstanceArgs($arDependencies);
    }

    public static function hasService(string $resolveService) : bool
    {
        return isset(self::$services[$resolveService]);
    }
}