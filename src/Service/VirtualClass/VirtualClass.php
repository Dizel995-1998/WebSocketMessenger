<?php

namespace Service\VirtualClass;

class VirtualClass
{
    /**
     * @var VirtualProperty[]
     */
    protected array $properties = [];

    /**
     * @var VirtualMethod[]
     */
    protected array $methods = [];

    public function __construct(
        protected string $name,
        protected ?string $extendsFrom = null,
        protected ?array $implementsInterfaces = null
    ) { }

    /**
     * @param VirtualProperty ...$virtualProperties
     * @return $this
     */
    public function addProperties(VirtualProperty ...$virtualProperties) : self
    {
        $this->properties = $virtualProperties;
        return $this;
    }

    /**
     * @param VirtualMethod ...$methods
     * @return $this
     */
    public function addMethods(VirtualMethod ...$methods) : self
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return VirtualMethod[]
     */
    public function getMethods() : array
    {
        return $this->methods;
    }

    public function hasMethods() : bool
    {
        return !empty($this->methods);
    }

    /**
     * @return VirtualProperty[]
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isExtended() : bool
    {
        return $this->extendsFrom !== null;
    }

    public function isImplements() : bool
    {
        return $this->implementsInterfaces !== null;
    }

    public function getExtendsFromClass() : ?string
    {
        return $this->extendsFrom;
    }

    public function getImplementsInterfaces() : ?array
    {
        return $this->implementsInterfaces;
    }
}