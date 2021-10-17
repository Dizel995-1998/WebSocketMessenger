<?php

namespace Lib\Database\Migration;

use Lib\Database\Column\BaseColumn;

class DiffSchemaDto
{
    protected array $createTables = [];
    protected array $deleteTables = [];
    protected array $createColumns = [];
    protected array $deleteColumns = [];

    public function addTableForCreate(Table $table) : self
    {
        $this->createTables[] = $table;
        return $this;
    }

    public function addTableForDelete(Table $table) : self
    {
        $this->deleteTables[] = $table;
        return $this;
    }

    public function addColumnsForCreate(BaseColumn ...$columns) : self
    {
        $this->createColumns = array_merge($this->createColumns, $columns);
        return $this;
    }

    public function addColumnsForDelete(BaseColumn ...$column) : self
    {
        $this->deleteColumns = array_merge($this->deleteColumns, $column);
        return $this;
    }

    /**
     * @return Table[]
     */
    public function getTablesForCreate() : array
    {
        return $this->createTables;
    }

    /**
     * @return Table[]
     */
    public function getTablesForDelete() : array
    {
        return $this->deleteTables;
    }

    /**
     * @return BaseColumn[]
     */
    public function getColumnsForCreate() : array
    {
        return $this->createColumns;
    }

    /**
     * @return BaseColumn[]
     */
    public function getColumnsForDelete() : array
    {
        return $this->deleteColumns;
    }
}