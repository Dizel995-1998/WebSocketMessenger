<?php

namespace Lib\Database\Relations;

/**
 * todo ввести параметр обязательности связи
 */
abstract class BaseRelation
{
    public function __construct(
        protected string $sourceColumn,
        protected string $sourceTable,
        protected string $targetColumn,
        protected string $targetTable
    ) {

    }

    public function getSourceTable() : string
    {
        return $this->sourceTable;
    }

    public function getTargetTable() : string
    {
        return $this->targetTable;
    }

    public function getSourceColumn() : string
    {
        return $this->sourceColumn;
    }

    public function getTargetColumn() : string
    {
        return $this->targetColumn;
    }
}