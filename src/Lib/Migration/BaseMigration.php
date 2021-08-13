<?php

namespace Lib\Migration;

class BaseMigration
{
    const PATH_TO_MIGRATIONS_FROM_DOCUMENT_ROOT = '';

    public function __construct()
    {

    }

    public function run(IMigration $migration, bool $rollback = false)
    {
        try {
            $rollback ? $migration->up() : $migration->down();
        } catch (\Throwable $e) {

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