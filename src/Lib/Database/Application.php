<?php

namespace Service\Database;

use Service\Database\Interfaces\IConnection;
use Service\Database\Interfaces\IDbResult;

class Application
{
    protected IConnection $connection;

    public function __construct(IConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $sql
     * @return IDbResult
     */
    public function query(string $sql) : IDbResult
    {
        return $this->connection->query($sql);
    }

    /**
     * @param string $sql
     * @return int|null
     */
    public function exec(string $sql) : ?int
    {
        return $this->connection->exec($sql);
    }
}