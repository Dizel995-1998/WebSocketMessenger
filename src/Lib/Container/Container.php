<?php

namespace Lib\Container;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Container implements ContainerInterface
{
    private array $services = [];

    public function setService(string $resolveService, string $currentService = null, array $args = null) : void
    {
        $this->services[$resolveService] = [
            'current_service' => $currentService ?: $resolveService,
            'args' => $args
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function getService(string $resolveService) : object
    {
        if (!$service = $this->services[$resolveService]['current_service']) {
            throw new RuntimeException(sprintf('Can\'t resolve service: %s', $resolveService));
        }

        $reflection = new ReflectionClass($service);
        $arDependencies = [];

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
                    if (!$primitiveTypeValue = $this->services[$resolveService]['args'][$dependency->getName()]) {
                        throw new RuntimeException('Can\'t resolve primitive dependencies');
                    }

                    $arDependencies[] = $primitiveTypeValue;
                    continue;
                }

                $arDependencies[] = $this->getService($dependency->getClass()->getName());
            }
        }

        return $reflection->newInstanceArgs($arDependencies);
    }

    public function hasService(string $resolveService) : bool
    {
        return isset($this->services[$resolveService]);
    }
}