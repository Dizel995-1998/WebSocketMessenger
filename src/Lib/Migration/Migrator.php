<?php

namespace Lib\Migration;

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
        if (!$this->dbConnection->beginTransaction()) {
            throw new \RuntimeException('Can\'t start transaction');
        }

        try {
            $rollback ? $migration->up() : $migration->down();

            if (!$this->dbConnection->commitTransaction()) {
                throw new \RuntimeException('Can\'t commit transaction');
            }

        } catch (\Throwable $e) {
            if (!$this->dbConnection->rollbackTransaction()) {
                throw new \RuntimeException('Can\'t rollback transaction');
            }
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