<?php

namespace Lib\Database\Migration;

use Lib\Database\Drivers\Interfaces\IConnection;
use Lib\Database\Reader\IReader;

class MigrationAutoWriter
{
    public function __construct(
        protected IReader $reader,
        protected IConnection $dbConnection
    ) {

    }

    protected function isExistClasses(array $classes) : void
    {
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('Entity class "%s" was not found', $class));
            }
        }
    }

    public function runSync(array $ormClasses) : void
    {
        $this->isExistClasses($ormClasses);
        $ormSchema = new Schema();

        /** Строим схему на основе ОРМ сущностей */
        foreach ($ormClasses as $class) {
            $this->reader->readEntity($class);
            $ormSchema->addTable(new Table($this->reader->getTableName(), $this->reader->getColumns()));
        }

        $dbSchema = $this->dbConnection->getSchema();
        $dtoDiff = Schema::diffSchemas($ormSchema, $dbSchema);

        /** Удаляем таблицы */
        foreach ($dtoDiff->getTablesForDelete() as $table) {
            $this->dbConnection->dropTable($table);
        }

        /** Удаляем колонки */
        foreach ($dtoDiff->getColumnsForDelete() as $column) {
            $this->dbConnection->dropColumn($column);
        }

        /** Создаём таблицы */
        foreach ($dtoDiff->getTablesForCreate() as $table) {
            $this->dbConnection->addTable($table);
        }

        /** Создаём колонки */
        foreach ($dtoDiff->getColumnsForCreate() as $column) {
            $this->dbConnection->addColumn($column);
        }
    }
}