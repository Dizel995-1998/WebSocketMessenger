<?php

namespace Service;

use Entity\MigrationTable;
use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Migration\IMigration;
use ReflectionException;
use RuntimeException;

class MigrationManager
{
    protected IConnection $connection;

    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws RuntimeException при неудачном фиксе правок в БД
     * @return void
     */
    protected function commitTransaction() : void
    {
        if (!$this->connection->commitTransaction()) {
            throw new \RuntimeException('Can\'t commit transaction');
        }
    }

    /**
     * @throws RuntimeException при неудачном откате правок в БД
     * @return void
     */
    protected function rollbackTransaction() : void
    {
        if (!$this->connection->rollbackTransaction()) {
            throw new RuntimeException('Can\'t rollback transaction');
        }
    }

    /**
     * @throws RuntimeException при не удачном начале транзакции
     * @return void
     */
    protected function beginTransaction() : void
    {
        if (!$this->connection->beginTransaction()) {
            throw new RuntimeException('Can\'t start transaction');
        }
    }

    /**
     * @param string $migrationClassName
     * @param bool $isMigrationUp
     * @throws ReflectionException
     */
    public function runMigration(string $migrationClassName, bool $isMigrationUp = true) : void
    {
        $migration = Container::getService($migrationClassName);
        $migrationNameForDb = basename(str_replace('\\', '/', get_class($migration)));

        if (!$migration instanceof IMigration) {
            throw new \InvalidArgumentException(sprintf('Migration must implements "%s" interface', IMigration::class));
        }

        $migrationEntityFoundByProp = MigrationTable::findByProperty(['name' => $migrationNameForDb]);

        if ($isMigrationUp && $migrationEntityFoundByProp) {
            throw new \RuntimeException('Миграция уже накатывалась');
        }

        if (!$isMigrationUp && !$migrationEntityFoundByProp) {
            throw new RuntimeException('Миграция не накатывалась, чтобы её откатывать');
        }

        $this->beginTransaction();

        try {
            if ($isMigrationUp) {
                $migration->up();
                // todo реализовать методы сетеры и гетеры и строить цепочки
                $migrationEntity = new MigrationTable();
                $migrationEntity->name = $migrationNameForDb;
                $migrationEntity->save();
            } else {
                $migration->down();
                $migrationEntityFoundByProp->delete();
            }

            $this->commitTransaction();

        } catch (\Throwable $e) {
            $this->rollbackTransaction();
            throw new RuntimeException($e->getMessage());
        }
    }
}