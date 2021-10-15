<?php

namespace Lib\Database\Drivers\Interfaces;

use Lib\Database\Drivers\DbResult;

interface IConnection
{
    public function __construct(
        string $user,
        string $password,
        string $host,
        string $dbName,
        int $port
    );

    public function exec(string $sql) : bool;

    public function query(string $sql) : DbResult;

    public function getLastInsertedId() : null|string;
}