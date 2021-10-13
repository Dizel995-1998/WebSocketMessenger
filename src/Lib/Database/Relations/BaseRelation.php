<?php

namespace Lib\Database\Relations;

abstract class BaseRelation
{
    public function __construct(
        protected string $sourceColumn,
        protected string $sourceTable,
        protected string $targetColumn,
        protected string $targetTable,
        protected string $sourceEntity,
        protected string $targetEntity
    ) {

    }

    public function getTargetEntity() : string
    {
        return $this->targetEntity;
    }

    public function getSourceEntity() : string
    {
        return $this->sourceEntity;
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