<?php

namespace Lib\Database;

use InvalidArgumentException;

class QueryBuilderSelector extends QueryBuilder
{
    const ALLOW_JOIN_TYPES = ['LEFT', 'INNER', 'RIGHT'];

    private string $selectQuery = '*';
    private string $filterQuery = '';
    private string $joinQuery = '';
    protected string $queryWhere;
    private string $groupQuery;
    private string $sortQuery;

    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
        $this->queryWhere = '1 = 1';
    }

    public function setSelect(array $arSelect) : self
    {
        $arResult = [];

        foreach ($arSelect as $alias => $field) {
            if (!preg_match('~((?<table>\S+)\.)?(?<field>\S+)~', $field, $matches)) {
                throw new InvalidArgumentException('Select fields have invalid value');
            }

            $arResult[] = is_numeric($alias) ?
                sprintf('%s.%s', $matches['table'] ?: $this->tableName, $matches['field']) :
                sprintf('%s.%s as %s', $matches['table'] ?: $this->tableName, $matches['field'], $alias);
        }

        $this->selectQuery = implode(', ', $arResult);
        return $this;
    }


    /**
     * TODO дубль кода в билдерах
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

    public function setJoin(string $tableName, string $tableAlias, string $joinType, array $reference) : self
    {
        $joinType = strtoupper($joinType);

        if (!$tableName) {
            throw new InvalidArgumentException('Table name cannot be empty');
        }

        if (!array_key_exists($joinType, array_flip(self::ALLOW_JOIN_TYPES))) {
            throw new InvalidArgumentException(sprintf('Dont supported so join type "%s", allow: %s',
                    $joinType,
                    implode(', ', self::ALLOW_JOIN_TYPES))
            );
        }

        // TODO пока нет возможности вписать в ON несколько условий

        if (!preg_match('~this.(?<this_key>\S+)~', key($reference), $thisEntity)) {
            throw new InvalidArgumentException('There is no reference to this.entity, use this.COLUMN_NAME');
        }

        if (!preg_match('~ref.(?<ref_key>\S+)~', current($reference), $refEntity)) {
            throw new InvalidArgumentException('There is no reference to ref.entity, use ref.COLUMN_NAME');
        }

        $this->joinQuery .= sprintf('%s JOIN %s as %s ON %s.%s = %s.%s ',
            $joinType,
            ($tableName),
            ($tableAlias),
            ($this->tableName),
            ($thisEntity['this_key']),
            ($tableAlias),
            ($refEntity['ref_key'])
        );

        return $this;
    }

    public function getQuery() : string
    {
        if (!$this->queryWhere) {
            throw new \RuntimeException('Filter must be set, before get query');
        }

        return sprintf('SELECT %s FROM %s %s WHERE %s',
            $this->selectQuery ?: '*',
            $this->tableName,
            $this->joinQuery,
            $this->queryWhere
        );
    }
}