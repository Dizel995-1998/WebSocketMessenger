<?php

namespace Lib\Database;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Database\Interfaces\IDbResult;

class Application
{
    protected IConnection $connection;

    /**
     * @return static
     * @throws \ReflectionException
     */
    public static function getInstance() : self
    {
        static $instance = null;
        return $instance ?: $instance = new self(Container::getService(IConnection::class));
    }

    protected function __construct(IConnection $connection)
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