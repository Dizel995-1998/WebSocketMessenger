<?php

namespace Lib\Database\Migration;

use Lib\Database\Drivers\FakeDriver;
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

    /**
     * @param array $ormClasses список классов ORM сущностей
     * @return string[] массив SQL запросов для синхронизации
     */
    public function runSync(array $ormClasses) : array
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

        $fakeDbConnection = new FakeDriver();

        /** Удаляем таблицы */
        foreach ($dtoDiff->getTablesForDelete() as $table) {
            $fakeDbConnection->dropTable($table);
        }

        /** Удаляем колонки */
        foreach ($dtoDiff->getColumnsForDelete() as $column) {
            $fakeDbConnection->dropColumn($column);
        }

        /** Создаём таблицы */
        foreach ($dtoDiff->getTablesForCreate() as $table) {
            $fakeDbConnection->addTable($table);
        }

        /** Создаём колонки */
        foreach ($dtoDiff->getColumnsForCreate() as $column) {
            $fakeDbConnection->addColumn($column);
        }

        return $fakeDbConnection->getQueries();
    }
}