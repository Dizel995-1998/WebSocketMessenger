<?php

namespace Lib\Database;

use RuntimeException;

class QueryBuilderInserter extends QueryBuilder
{
    protected array $fields;
    protected array $values;

    /**
     * @param array $fields
     * @return self
     */
    public function insert(array $fields) : self
    {
        // todo проверка на ассоциативный массив

        foreach ($fields as $field => $value) {
            $this->fields[] = $field;
            $this->values[] = is_int($value) ? $value : $this->escapeString($value ?: '');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        if (empty($this->fields) || empty($this->values)) {
            throw new RuntimeException('You have to call "insert" method before get query');
        }

        return sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $this->tableName,
            implode(',', $this->fields),
            implode(',', $this->values)
        );
    }
}