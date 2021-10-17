<?php

namespace Lib\Database\Migration;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Lib\Database\Column\BaseColumn;

class Schema
{
    protected array $tables;

    public function __construct(Table ...$tables)
    {
        $this->tables = $tables;
    }

    public function addTable(Table $table) : self
    {
        $this->tables[] = $table;
        return $this;
    }

    /**
     * @return Table[]
     */
    public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @param Table $table
     * @param BaseColumn $column
     * @return bool
     */
    #[Pure] protected static function isExistColumn(Table $table, BaseColumn $column) : bool
    {
        foreach ($table->getColumns() as $masterColumn) {
            if ($masterColumn->getName() == $column->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Пока возвращает разницу лишь на основе отсутствия колонки в slave и её присутствие в master, нужна ещё проверка типов
     * @param Table $master
     * @param Table $slave
     * @return BaseColumn[]
     */
    protected static function diffBetweenTables(Table $master, Table $slave) : array
    {
        $missingColumns = [];

        foreach ($master->getColumns() as $column) {
            if (!self::isExistColumn($slave, $column)) {
                $missingColumns[] = $column;
            }
        }

        return $missingColumns;
    }

    /**
     * @param Schema $schemaHaystack
     * @param string $tableName
     * @return Table|null
     */
    #[Pure] protected static function findTableInSchema(Schema $schemaHaystack, string $tableName) : ?Table
    {
        foreach ($schemaHaystack->getTables() as $table) {
            if ($table->getName() == $tableName) {
                return $table;
            }
        }

        return null;
    }

    /**
     * Возвращает схему с таблицами которые есть в master схеме и которые отсутствуют в slave
     * Нужна другая возвращаемая структура, таблицы у которых есть дифф и этот дифф нужно воссоздать, и таблицы у которых нужно дифф удалить
     * @param Schema $master
     * @param Schema $slave
     * @return array
     */
    #[ArrayShape(['add' => "array", 'remove' => "array"])] public static function diffSchemas(Schema $master, Schema $slave) : array
    {
        $addTables = [];
        $removeTables = [];

        /*** Ищем таблицы и колонки которые есть в master схеме и которых нет в slave схеме */
        foreach ($master->getTables() as $masterTable) {
            if (!$table = self::findTableInSchema($slave, $masterTable->getName())) {
                $addTables[] = $masterTable;
                continue;
            }

            if (!empty($diffColumns = self::diffBetweenTables($masterTable, $table))) {
                $addTables[] = new Table($masterTable->getName(), $diffColumns);
            }
        }

        /*** Ищем таблицы и колонки которые есть в slave схеме и которых нет в master схеме */
        foreach ($slave->getTables() as $slaveTable) {
            if (!$table = self::findTableInSchema($master, $slaveTable->getName())) {
                $removeTables[] = $slaveTable;
                continue;
            }

            if (!empty($diffColumns = self::diffBetweenTables($slaveTable, $table))) {
                $removeTables[] = new Table($slaveTable->getName(), $diffColumns);
            }
        }

        return [
            'add' => $addTables,
            'remove' => $removeTables
        ];
    }
}