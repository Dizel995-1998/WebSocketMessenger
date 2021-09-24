<?php

namespace Lib\Database\Column;

abstract class BaseColumn
{
    /**
     * @var string
     */
    protected string $columnName;

    /**
     * @var mixed
     */
    protected $columnValue;

    /**
     * @var bool
     */
    protected bool $isPrimaryKey;

    /**
     * TODO правильно ли в колонке хранить значение, ведь значение есть в колонки у строки, а не у абстрактной колонки
     * @param string $columnName
     * @param null $columnValue
     * @param bool $isPrimaryKey
     */
    public function __construct(string $columnName, $columnValue = null, bool $isPrimaryKey = false)
    {
        $this->columnName = $columnName;
        $this->columnValue = $columnValue;
        $this->isPrimaryKey = $isPrimaryKey;
    }

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function getName(): string
    {
        return $this->columnName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->columnValue;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value): self
    {
        $this->columnValue = $value;
        return $this;
    }

    abstract public function getType(): string;
}
