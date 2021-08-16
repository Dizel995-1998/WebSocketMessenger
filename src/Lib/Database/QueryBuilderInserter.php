<?php

namespace Lib\Database;

use RuntimeException;

class QueryBuilderInserter extends QueryBuilder
{
    protected array $fields;
    protected array $values;

    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
    }

    /**
     * @param array $fields
     */
    public function insert(array $fields)
    {
        // todo проверка на ассоциативный массив

        foreach ($fields as $field => $value) {
            $this->fields[] = $field;
            $this->values[] = is_string($value) ? $this->escapeString($value) : $value;
        }
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