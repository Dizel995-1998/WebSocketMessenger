<?php

namespace Lib\Database\Drivers;

class FakeDriver extends PdoDriver
{
    protected array $queries = [];

    public function __construct() {}

    /**
     * @return string[]
     */
    public function getQueries() : array
    {
        return $this->queries;
    }

    public function exec(string $sql): bool
    {
        $this->queries[] = $sql;
        return true;
    }

    public function query(string $sql): DbResult
    {
        $this->queries[] = $sql;
        return new DbResult([]);
    }
}