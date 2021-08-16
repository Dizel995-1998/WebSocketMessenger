<?php

namespace Lib\Database;

abstract class QueryBuilder
{
    protected string $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * TODO исправить баг с экранированием колонок
     * Экранирует строку ( не уверен в правильности работоспособности )
     * @param string $stringForEscape
     * @return string
     */
    protected function escapeString(string $stringForEscape) : string
    {
        return is_numeric($stringForEscape) ?
            $stringForEscape :
            "'$stringForEscape'";
    }

    abstract public function getQuery() : string;
}