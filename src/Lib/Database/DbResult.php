<?php

namespace Service\Database;

use Service\Database\Interfaces\IDbResult;

class DbResult implements IDbResult
{
    private array $arRows;

    public function __construct(array $arData)
    {
        $this->arRows = $arData;
    }

    public function fetch(): ?array
    {
        $current = current($this->arRows);
        next($this->arRows);
        return $current ?: null;
    }
}