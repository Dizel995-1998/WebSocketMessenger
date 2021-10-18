<?php

namespace Lib\Database\Collection;

use Lib\Database\EntityManager\EntityManager;

class Proxy
{
    protected bool $initialized = false;
    protected string $originClassName;
    protected \Closure $callbackOnBeforeCallAnyMethod;
    protected EntityManager $entityManager;
    protected $identifier;
    protected object $originObject;

    public function __construct(string $originClassName, EntityManager $entityManager)
    {
        if (!class_exists($originClassName)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" was not found', $originClassName));
        }

        $this->entityManager = $entityManager;
        $this->originClassName = $originClassName;
        $this->originObject = (new \ReflectionClass($originClassName))->newInstanceWithoutConstructor();
    }

    public function onBeforeCallAnyMethod(\Closure $initializer, array $args) : self
    {
        $this->callbackOnBeforeCallAnyMethod = $initializer;
        $this->identifier = $args;
        return $this;
    }

    public function __call(string $name, array $args)
    {
        if (!$this->initialized) {
            $this->originObject = $this->callbackOnBeforeCallAnyMethod->call($this, $this->identifier);
        }

        $this->initialized = true;
        return $this->originObject->$name($args);
    }
}