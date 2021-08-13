<?php

namespace Lib\Database\Adapters;

use Lib\Database\Interfaces\IDbResult;

class PdoResult implements IDbResult
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