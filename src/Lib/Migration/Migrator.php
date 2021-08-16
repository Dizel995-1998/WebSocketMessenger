<?php

namespace Lib\Migration;

use Entity\MigrationTable;
use InvalidArgumentException;
use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use RuntimeException;
use Throwable;

class Migrator
{
    protected IConnection $dbConnection;
    protected string $pathToMigrations;

    public function __construct(IConnection $dbConnection, string $pathToMigrations)
    {
        if (!is_dir($pathToMigrations)) {
            throw new InvalidArgumentException(sprintf('Migration dir: %s not found', $pathToMigrations));
        }

        $this->dbConnection = $dbConnection;
        $this->pathToMigrations = $pathToMigrations;
    }

    public function run(IMigration $migration, bool $rollback = false)
    {
        $migrationName = basename(str_replace('\\', '/', get_class($migration)));
        /** TODO хардкод колонки  */
        if (!$rollback && MigrationTable::getList(['filter' => ['MIGRATION_NAME' => $migrationName]])->fetch()) {
            throw new RuntimeException(sprintf('Миграция %s уже была выполнена', $migrationName));
        }

        if (!$this->dbConnection->beginTransaction()) {
            throw new RuntimeException('Can\'t start transaction');
        }

        try {
            if ($rollback) {
                try {
                    (MigrationTable::findByColumnOrFail('MIGRATION_NAME', $migrationName))->delete();
                } catch (Throwable $e) {
                    throw new RuntimeException('Невозможно откатить миграцию которая не накатывалась');
                }
            } else {
                $dbConn = Container::getService(IConnection::class);
                $query = sprintf('INSERT INTO %s (MIGRATION_NAME) VALUES (%s)', MigrationTable::getTableName(), $dbConn->quote($migrationName));
                $dbConn->exec($query);
            }

            $rollback ? $migration->down() : $migration->up();

            if (!$this->dbConnection->commitTransaction()) {
                throw new RuntimeException('Can\'t commit transaction');
            }

        } catch (Throwable $e) {
            if (!$this->dbConnection->rollbackTransaction()) {
                throw new RuntimeException('Can\'t rollback transaction');
            }

            throw new RuntimeException(sprintf('Migration error: %s', $e->getMessage()));
        }
    }
}