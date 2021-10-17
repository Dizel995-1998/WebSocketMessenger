<?php

namespace Lib\Database\Drivers;

use Lib\Database\Migration\Schema;

class FakeDriver extends PdoDriver
{
    public function exec(string $sql): bool
    {
        echo $sql . '<br/>';
        return true;
    }

    public function query(string $sql): DbResult
    {
        echo $sql . '<br/>';
        return new DbResult([]);
    }
}