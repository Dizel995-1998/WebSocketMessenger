<?php

namespace Service\Database\Interfaces;

interface IDbResult
{
    public function fetch() : ?array;
}