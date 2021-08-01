<?php

namespace Lib\Database;

use InvalidArgumentException;

class QueryBuilder
{
    const ALLOW_JOIN_TYPES = ['LEFT', 'INNER', 'RIGHT'];

    private string $selectQuery = '*';
    private string $filterQuery = '';
    private string $joinQuery = '';
    private string $groupQuery;
    private string $sortQuery;
    private string $fromTable;

    public function __construct(string $tableName)
    {
        $this->fromTable = $tableName;
    }

    /**
     * TODO исправить баг с экранированием колонок
     * Экранирует строку ( не уверен в правильности работоспособности )
     * @param string $stringForEscape
     * @return string
     */
    private function escapeString(string $stringForEscape) : string
    {
        return $stringForEscape;
    }

    // TODO приведение колонок из массива и таблиц к одному регистру

    public function setSelect(array $arSelect) : self
    {
        $arResult = [];

        foreach ($arSelect as $alias => $field) {
            if (!preg_match('~((?<table>\S+)\.)?(?<field>\S+)~', $field, $matches)) {
                throw new InvalidArgumentException('Select fields have invalid value');
            }

            $arResult[] = is_numeric($alias) ?
                sprintf('%s.%s', $matches['table'] ?: $this->fromTable, $matches['field']) :
                sprintf('%s.%s as %s', $matches['table'] ?: $this->fromTable, $matches['field'], $alias);
        }

        $this->selectQuery = implode(', ', $arResult);
        return $this;
    }

    /**
     * TODO реализовать операции больше, меньше, не равно
     * @param array $arFilter
     * @return $this
     */
    public function setFilter(array $arFilter) : self
    {
        $buildFilter = function ($arFilter) : array {
            $arResult = [];

            foreach ($arFilter as $field => $value) {
                if (!preg_match('~(?<comparison_operator><|>|!=)?(?<column_name>\S+)~', $field, $matches)) {
                    throw new InvalidArgumentException(sprintf('Incorrect filter field: %s', $field));
                }

                $matches['comparison_operator'] = $matches['comparison_operator'] ?: '=';
                $matches['column_name'] = $this->fromTable . '.' . $matches['column_name'];

                $arResult[] = is_array($value) ?
                    sprintf('%s IN (%s)', $matches['column_name'], $this->escapeString(implode(',', $value))) :
                    sprintf('%s %s %s', $matches['column_name'], $matches['comparison_operator'], $this->escapeString($value));
            }

            return $arResult;
        };

        $this->filterQuery = $arFilter['LOGIC_OR'] ?
            implode(' OR ', $buildFilter($arFilter['LOGIC_OR'])) :
            implode(' AND ', $buildFilter($arFilter));

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

        $this->joinQuery .= sprintf(' %s JOIN %s as %s ON %s.%s = %s.%s ',
            $joinType,
            $this->escapeString($tableName),
            $this->escapeString($tableAlias),
            $this->escapeString($this->fromTable),
            $this->escapeString($thisEntity['this_key']),
            $this->escapeString($tableAlias),
            $this->escapeString($refEntity['ref_key'])
        );

        return $this;
    }

    public function getQuery() : string
    {
        $query = 'SELECT ' . $this->selectQuery ?: '*' . PHP_EOL;
        $query .= ' FROM ' . $this->fromTable . PHP_EOL;
        $query .= $this->joinQuery ?: '';

        // TODO какой то костыль
        if ($this->filterQuery) {
            $query .= 'WHERE ' . $this->filterQuery . PHP_EOL;
        }

        return $query;
    }
}