<?php

namespace Lib\Database\Drivers;

use Lib\Database\Column\BaseColumn;
use Lib\Database\Drivers\Interfaces\IConnection;
use Lib\Database\Migration\Table;

abstract class BaseDriver implements IConnection
{
    public function dropColumn(BaseColumn $column): void
    {
        $this->exec(sprintf('ALTER TABLE %s DROP COLUMN %s', $column->getTableName(), $column->getName()));
    }

    public function addColumn(BaseColumn $column): void
    {
        $this->exec(sprintf('ALTER TABLE %s ADD %s %s',
            $column->getTableName(),
            $column->getName(),
            $column->getType())
        );
    }

    public function dropTable(Table $table): void
    {
        $this->exec(sprintf('DROP TABLE %s', $table->getName()));
    }

    /**
     * todo экранирование
     * @param Table $table
     */
    public function addTable(Table $table): void
    {
        $sql = sprintf('CREATE TABLE %s (%s)',
            $table->getName(),
            implode(',', array_map(function (BaseColumn $column) {
                return $column->getName() . ' ' . $column->getType();
        }, $table->getColumns())));

        $this->exec($sql);
    }
}