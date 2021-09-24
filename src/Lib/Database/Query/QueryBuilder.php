<?php

namespace Lib\Database\Query;

class QueryBuilder
{
    protected array $arMockData;

    public function __construct(array $arMockData)
    {
        $this->arMockData = $arMockData;
    }

    public function getSomeData(): array
    {
        return $this->arMockData;
    }
}