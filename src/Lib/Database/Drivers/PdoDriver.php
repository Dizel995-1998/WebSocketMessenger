<?php

namespace Lib\Database\Drivers;


use Lib\Database\Drivers\Exceptions\CannotConnectToDataBase;
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

    /**
     * @throws CannotConnectToDataBase
     */
    protected function getConnection() : PDO
    {
        static $connection;

        if (!isset($connection)) {
            try {
                $conn = sprintf('%s:host=%s;dbname=%s', self::DB_SQL_TYPE, $this->host, $this->dbName);
                $connection = new PDO($conn, $this->user, $this->password);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $connection->query("set names 'utf8'");
            } catch (\PDOException $PDOException) {
                throw new CannotConnectToDataBase($PDOException->getMessage());
            }
        }

        return $connection;
    }

    public function exec(string $sql): bool
    {
        // todo: обернуть исключения PDO в свои
        return (bool) $this->getConnection()->exec($sql);
    }

    public function query(string $sql): DbResult
    {
        // todo: обернуть исключения PDO в свои
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