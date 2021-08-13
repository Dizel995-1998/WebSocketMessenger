<?php

namespace Lib\Database\Interfaces;

interface IConnection
{
    public function exec(string $sql) : ?int;

    public function query(string $sql) : IDbResult;

    public function beginTransaction() : bool;

    public function isTransaction() : bool;

    public function commitTransaction() : bool;

    public function rollbackTransaction() : bool;
}