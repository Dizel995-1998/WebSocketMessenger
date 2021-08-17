<?php

namespace Migration;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Migration\IMigration;
use Migration;

class CreateUserEntity implements IMigration
{
    /** Временное решение, пока не напишу авто врайтер миграций */
    const FIELDS_MAP = [
        'ID integer PRIMARY KEY AUTO_INCREMENT',
        'NAME varchar(100) NOT NULL',
        'LOGIN varchar(100) NOT NULL',
        'PASSWORD_HASH varchar(100) NOT NULL',
        'PICTURE_URL varchar(100) NOT NULL'
    ];

    protected string $tableName;
    protected IConnection $dbConnection;

    public function __construct()
    {
        $this->tableName = \Entity\UserTable::getTableName();
        $this->dbConnection = Container::getService(IConnection::class);
    }

    public function up(): void
    {
        $this->dbConnection->exec(sprintf('CREATE TABLE %s (%s)', $this->tableName, implode(',', self::FIELDS_MAP)));
    }

    public function down(): void
    {
        $this->dbConnection->exec(sprintf('DROP TABLE %s', $this->tableName));
    }
}