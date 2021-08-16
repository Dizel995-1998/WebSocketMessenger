<?php

namespace Lib\Database;

use InvalidArgumentException;
use RuntimeException;

class QueryBuilderUpdater extends QueryBuilder
{
    protected array $querySetter;
    protected string $queryWhere;

    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
        $this->queryWhere = '1 = 1';
    }

    /**
     * TODO перейти на PHP 8.0 для избавления от валидации множественных типов
     * @param string $column
     * @param string|int $value
     * @return self
     */
    public function set(string $column, $value) : self
    {
        if (!is_string($value) && !is_int($value)) {
            throw new InvalidArgumentException('Value must be one of type: int, string');
        }

        $this->querySetter[] = $column . '=' . (is_string($value) ? $this->escapeString($value) : $value);
        return $this;
    }

    /**
     * На данный момент доступны лишь простые проверки по типу равенства или вхождению в диапазон
     * @param string $column
     * @param string|int|array $value
     * @param bool $isOr - тип условия, OR или AND
     * @return self
     */
    public function where(string $column, $value, bool $isOr = false) : self
    {
        if (
            !is_array($value) &&
            !is_numeric($value) &&
            !is_string($value)
        ) {
            throw new InvalidArgumentException('Value param must be one of type: string, array, int');
        }

        if (is_array($value)) {
            $this->queryWhere .= sprintf(' %s %s IN (%s)',
                ($isOr ? 'OR' : 'AND'),
                $column,
                implode(',', $value)
            );
        }

        if (is_string($value)) {
            $this->queryWhere .= sprintf(' %s %s = %s',
                ($isOr ? 'OR' : 'AND'),
                $column,
                $this->escapeString($value)
            );
        }

        if (is_int($value)) {
            $this->queryWhere .= sprintf(' %s %s = %s',
                ($isOr ? 'OR' : 'AND'),
                $column,
                $value
            );
        }

        return $this;
    }

    public function getQuery() : string
    {
        if (empty($this->querySetter)) {
            throw new RuntimeException('Can not generate query without set methods');
        }

        if (empty($this->queryWhere)) {
            throw new RuntimeException('Can not generate query without where condition');
        }

        return sprintf('UPDATE %s SET %s WHERE %s', $this->tableName, implode(',', $this->querySetter), $this->queryWhere);
    }
}