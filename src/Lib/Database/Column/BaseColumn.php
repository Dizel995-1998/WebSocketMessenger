<?php

namespace Lib\Database\Column;

abstract class BaseColumn
{
    /**
     * @var string
     */
    protected string $columnName;

    /**
     * @var bool
     */
    protected bool $isPrimaryKey = false;

    /**
     * @var bool
     */
    protected bool $isNullable;

    /**
     * @var string
     */
    protected string $tableName;

    /**
     * @var string
     */
    protected string $entityClassName;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @param string $columnName
     * @param string $tableName
     * @param string $entityClassName
     * @param bool $isNullable
     * @param null $defaultValue
     */
    public function __construct(
        string $columnName,
        string $tableName,
        string $entityClassName,
        bool $isNullable = true,
        $defaultValue = null
    ) {
        $this->entityClassName = $entityClassName;
        $this->columnName = $columnName;
        $this->isNullable = $isNullable;
        $this->tableName = $tableName;
        $this->defaultValue = $defaultValue;
    }

    public function getEntityClassName() : string
    {
        return $this->entityClassName;
    }

    public function getTableName() : string
    {
        return $this->tableName;
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
        return $this->columnName;
    }

    abstract public function getType(): string;
}
