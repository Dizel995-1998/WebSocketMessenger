<?php

namespace Lib\Database;

class ArResult
{
    private ?array $arData;

    public function __construct(?array $arData = null)
    {
        $this->arData = $arData;
    }


    public function fetch()
    {
        $current = current($this->arData);
        next($this->arData);
        return $current;
    }
}