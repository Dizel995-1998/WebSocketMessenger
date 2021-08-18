<?php

namespace Migration;

use Lib\Database\Interfaces\IConnection;use Lib\Migration\IMigration;

class CreateChatEntity implements IMigration
{
    /** TODO Временное решение, пока не напишу авто врайтер миграций */
    const FIELDS_MAP = [
        'ID integer PRIMARY KEY AUTO_INCREMENT',
        'NAME varchar(100) NOT NULL'
    ];

    protected string $tableName;
    private IConnection $dbConnection;
   
    public function __construct(IConnection $dbConnection)
    {
        $this->tableName = \Entity\Chat::getTableName();
        $this->dbConnection = $dbConnection;
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