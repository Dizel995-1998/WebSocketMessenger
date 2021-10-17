<?php

namespace Lib\Database\Migration;

use InvalidArgumentException;
use Lib\Database\Column\BaseColumn;

class Table
{
    /**
     * @param string $name
     * @param BaseColumn[] $columns
     */
    public function __construct(protected string $name, protected array $columns)
    {
        $this->validateColumns($this->columns);
    }

    protected function validateColumns(array $columns) : void
    {
        foreach ($columns as $column) {
            if (!$column instanceof BaseColumn) {
                throw new InvalidArgumentException(
                    sprintf('Colums must consist only "%s" type or child from it, "%s" given', BaseColumn::class, gettype($column))
                );
            }
        }
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getIndexes() : array
    {
        return [];
    }

    /**
     * @return BaseColumn[]
     */
    public function getColumns() : array
    {
        return $this->columns;
    }
}
