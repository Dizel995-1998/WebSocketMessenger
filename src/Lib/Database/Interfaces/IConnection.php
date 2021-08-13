<?php

namespace Service\Database\Interfaces;

interface IConnection
{
    public function exec(string $sql) : ?int;

    public function query(string $sql) : IDbResult;
}