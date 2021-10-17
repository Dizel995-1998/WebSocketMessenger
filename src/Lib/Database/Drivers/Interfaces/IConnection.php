<?php

namespace Lib\Database\Drivers\Interfaces;

use Lib\Database\Column\BaseColumn;
use Lib\Database\Drivers\DbResult;
use Lib\Database\Migration\Schema;
use Lib\Database\Migration\Table;

interface IConnection
{
    public function exec(string $sql) : bool;

    public function query(string $sql) : DbResult;

    public function getLastInsertedId() : null|string;

    public function getSchema() : Schema;

    public function dropColumn(BaseColumn $column) : void;

    public function addColumn(BaseColumn $column) : void;

    public function dropTable(Table $table) : void;

    public function addTable(Table $table) : void;
}