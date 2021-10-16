<?php

namespace Lib\Database\Column;

abstract class BaseColumn
{
    /**
     * @param string $name
     * @param bool $isNullable
     * @param bool $isPrimaryKey
     * @param null $defaultValue
     */
    public function __construct(
        protected string $name,
        protected bool $isNullable = true,
        protected bool $isPrimaryKey = false,
        protected $defaultValue = null
    ) {

    }

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function isNullable() : bool
    {
        return $this->isNullable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    // todo: вернуть что за тип, тот который в БД?
    // todo: UPD, если да - сделать класс СУБД типов
    abstract public function getType(): string;
}
