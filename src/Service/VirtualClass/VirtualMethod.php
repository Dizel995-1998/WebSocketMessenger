<?php

namespace Service\VirtualClass;

class VirtualMethod
{
    const NOTHING_RETURN = 'none';

    /**
     * @var VirtualArgument[]
     */
    protected array $args = [];

    public function __construct(protected string $name, protected string $modify = 'public', protected string $returnType = self::NOTHING_RETURN)
    {
        // todo валидация модификатора доступа
        // todo валидация returnType
    }

    public function getModified() : string
    {
        return $this->modify;
    }

    /**
     * @param VirtualArgument ...$args
     * @return $this
     */
    public function addArgs(VirtualArgument ...$args) : self
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return VirtualArgument[]
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    public function getName() : string
    {
        return $this->name;
    }
}