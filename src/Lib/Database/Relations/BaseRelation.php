<?php

namespace Lib\Database\Relations;

use Lib\Database\Column\BaseColumn;

abstract class BaseRelation
{
    protected BaseColumn $sourceColumn;
    protected BaseColumn $targetColumn;

    /**
     * @param BaseColumn $sourceColumn
     * @param BaseColumn $targetColumn
     */
    public function __construct(
        BaseColumn $sourceColumn,
        BaseColumn $targetColumn
    ) {
        $this->sourceColumn = $sourceColumn;
        $this->targetColumn = $targetColumn;
    }

    public function getSourceColumn() : BaseColumn
    {
        return $this->sourceColumn;
    }

    public function getTargetColumn() : BaseColumn
    {
        return $this->targetColumn;
    }
}