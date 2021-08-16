<?php

namespace Migration;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Migration\IMigration;

class MigrationTable implements IMigration
{
    private string $tableName;

    // TODO костыль
    private IConnection $dbConnection;

    public function __construct()
    {
        $this->tableName = \Entity\MigrationTable::getTableName();
        $this->dbConnection = Container::getService(IConnection::class);
    }

    public function up(): void
    {
        $this->dbConnection->exec(sprintf('CREATE TABLE %s (ID integer PRIMARY KEY AUTO_INCREMENT, MIGRATION_NAME varchar(100) NOT NULL)', $this->tableName));
    }

    public function down(): void
    {

    }
}