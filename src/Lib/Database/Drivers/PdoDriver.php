<?php

namespace Lib\Database\Drivers;


use Lib\Database\Drivers\Interfaces\IConnection;
use PDO;

class PdoDriver implements IConnection
{
    const DB_SQL_TYPE = 'mysql';

    public function __construct(
        protected string $user,
        protected string $password,
        protected string $host,
        protected string $dbName,
        protected int $port
    ) {

    }

    protected function getConnection() : PDO
    {
        static $connection;

        // fixme: обернуть исключения PDO в свои
        if (!isset($connection)) {
            $conn = sprintf('%s:host=%s;dbname=%s', self::DB_SQL_TYPE, $this->host, $this->dbName);
            $connection = new PDO($conn, $this->user, $this->password);
        }

        return $connection;
    }

    public function exec(string $sql): bool
    {
        return (bool) $this->getConnection()->exec($sql);
    }

    public function query(string $sql): DbResult
    {
        $db = $this->getConnection()->query($sql);

        return $db ?
            new DbResult($db->fetchAll(PDO::FETCH_ASSOC)) :
            new DbResult([]);
    }

    public function getLastInsertedId(): null|string
    {
        return $this->getConnection()->lastInsertId() ?: null;
    }
}