<?php

namespace Entity;

use Lib\Database\DataManager;

class MigrationTable extends DataManager
{
    /**
     * @ORM column_name ID
     * @var int
     */
    public int $id;

    /**
     * @ORM column_name MIGRATION_NAME
     * @var string
     */
    public string $name;

    public static function getTableName(): string
    {
        return 'executed_migrations';
    }
}