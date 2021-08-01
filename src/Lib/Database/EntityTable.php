<?php

namespace Lib\Database;

abstract class EntityTable
{
    abstract public function getTableName() : string;
}