<?php

namespace Lib\Database\Interfaces;

interface IDbResult
{
    public function fetch() : ?array;
}