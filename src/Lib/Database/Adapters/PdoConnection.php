<?php

namespace Lib\Database\Adapters;

use Lib\Database\Interfaces\IConnection;
use Lib\Database\Interfaces\IDbResult;
use PDO;

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
        return new PdoResult($this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function isTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * @return bool
     */
    public function commitTransaction(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * @return bool
     */
    public function rollbackTransaction(): bool
    {
        return $this->getConnection()->rollBack();
    }
}