<?php

namespace Lib\Database\Relations;

abstract class BaseRelation
{
    protected string $sourceTable;
    protected string $targetTable;
    protected string $sourceColumn;
    protected string $targetColumn;
    protected string $targetClassName;
    // todo скорее всего не нужно
    protected string $sourceClassName;

    /**
     * TODO можно заменить строковые значениям - двумя обьектами колонками, и в обьект колонки ввести принадлежность таблице
     * @param string $sourceColumn
     * @param string $sourceTable
     * @param string $targetColumn
     * @param string $targetTable
     * @param string $targetClassName
     */
    public function __construct(
        string $sourceColumn,
        string $sourceTable,
        string $targetColumn,
        string $targetTable,
        string $targetClassName
    )
    {
        $this->sourceColumn = $sourceColumn;
        $this->sourceTable = $sourceTable;
        $this->targetColumn = $targetColumn;
        $this->targetTable = $targetTable;
        $this->targetClassName = $targetClassName;
    }

    public function getTargetClassName(): string
    {
        return $this->targetClassName;
    }

    public function getSourceTable(): string
    {
        return $this->sourceTable;
    }

    public function getTargetTable(): string
    {
        return $this->targetTable;
    }

    public function getSourceColumn(): string
    {
        return $this->sourceColumn;
    }

    public function getTargetColumn(): string
    {
        return $this->targetColumn;
    }
}