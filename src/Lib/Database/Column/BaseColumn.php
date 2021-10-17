<?php

namespace Lib\Database\Column;

abstract class BaseColumn
{
    const DEFAULT_LENGTH = 50;

    /**
     * @param string $name
     * @param bool $isPrimaryKey
     * @param bool $isNullable
     * @param int|null $length
     * @param null $defaultValue
     */
    public function __construct(
        protected string $name,
        protected string $tableName,
        protected bool $isPrimaryKey = false,
        protected bool $isNullable = true,
        protected ?int $length = null,
        protected $defaultValue = null
    ) {
        $this->length = $length ?? self::DEFAULT_LENGTH;
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

    public function getTableName() : string
    {
        return $this->tableName;
    }

    /**
     * Возвращает тип данных формата СУБД
     * @return string
     */
    abstract public function getType(): string;
}
