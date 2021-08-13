<?php

namespace Service\Database;

use PDO;
use Service\Database\Interfaces\IConnection;
use Service\Database\Interfaces\IDbResult;

class PdoConnection implements IConnection
{
    protected string $dbHost;
    protected string $dbUser;
    protected string $dbPassword;
    protected string $dbName;

    public function __construct(
        string $dbHost,
        string $dbUser,
        string $dbPassword,
        string $dbName
    ) {
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;
    }

    /**
     * @return PDO
     */
    private function getConnection() : PDO
    {
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        static $instance = null;
        return $instance ?: $instance = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPassword, $options);
    }

    /**
     * @param string $sql
     * @return int|null
     */
    public function exec(string $sql): ?int
    {
        return (int) $this->getConnection()->exec($sql);
    }

    /**
     * @param string $sql
     * @return IDbResult
     */
    public function query(string $sql): IDbResult
    {
        return new DbResult($this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }
}