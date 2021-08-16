<?php

namespace Lib\Database;

class QueryBuilderDeleter extends QueryBuilderUpdater
{
    public function getQuery(): string
    {
        return sprintf('DELETE FROM %s WHERE %s', $this->tableName, $this->queryWhere);
    }
}