<?php

namespace Lib\Migration;

use Entity\MigrationTable;
use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;

class Migrator
{
    protected IConnection $dbConnection;
    protected string $pathToMigrations;

    public function __construct(IConnection $dbConnection, string $pathToMigrations)
    {
        if (!is_dir($pathToMigrations)) {
            throw new \InvalidArgumentException(sprintf('Migration dir: %s not found', $pathToMigrations));
        }

        $this->dbConnection = $dbConnection;
        $this->pathToMigrations = $pathToMigrations;
    }

    public function run(IMigration $migration, bool $rollback = false)
    {
        /** TODO хардкод колонки  */
        if (!$rollback && MigrationTable::getList(['filter' => ['MIGRATION_NAME' => get_class($migration)]])->fetch()) {
            throw new \RuntimeException(sprintf('Migration %s was already executed', get_class($migration)));
        }

        if (!$this->dbConnection->beginTransaction()) {
            throw new \RuntimeException('Can\'t start transaction');
        }

        try {
            $rollback ? $migration->down() : $migration->up();

            if ($rollback) {
                $id = MigrationTable::getList(['filter' => ['NAME' => get_class($migration)]])->fetch()['ID'];
                MigrationTable::findById($id)->delete();
            } else {
                $dbConn = Container::getService(IConnection::class);
                $dbConn->exec(sprintf('INSERT INTO %s (MIGRATION_NAME) VALUES %s', MigrationTable::getTableName(), $dbConn->quote(get_class($migration))));
            }

            if (!$this->dbConnection->commitTransaction()) {
                throw new \RuntimeException('Can\'t commit transaction');
            }

        } catch (\Throwable $e) {
            if (!$this->dbConnection->rollbackTransaction()) {
                throw new \RuntimeException('Can\'t rollback transaction');
            }

            throw new \RuntimeException(sprintf('Migration error: %s', $e->getMessage()));
        }
    }

    /**
     * TODO реализовать тип коллекций
     * Должен принимать коллекцию миграций
     */
    public function runAll()
    {
    }
}