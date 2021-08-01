<?php

namespace Lib\Database;

use InvalidArgumentException;

class QueryBuilder
{
    const ALLOW_JOIN_TYPES = ['LEFT', 'INNER', 'RIGHT'];

    private EntityTable $entity;

    private string $selectQuery = '';
    private string $filterQuery = '';
    private string $joinQuery = '';
    private string $groupQuery;
    private string $sortQuery;

    public function __construct(EntityTable $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Экранирует строку ( не уверен в правильности работоспособности )
     * @param string $stringForEscape
     * @return string
     */
    private function escapeString(string $stringForEscape) : string
    {
        return is_numeric($stringForEscape) ?
            addslashes($stringForEscape) :
            "'". addslashes($stringForEscape) . "'";
    }

    public function setSelect(array $arSelect) : self
    {
        $arResult = [];

        foreach ($arSelect as $alias => $field) {
            $arResult[] = is_numeric($alias) ? $field : $field . ' AS ' . $alias;
        }

        $this->selectQuery = implode(', ', $arResult);
        return $this;
    }

    public function setFilter(array $arFilter) : self
    {
        $buildFilter = function ($arFilter) : array {
            $arResult = [];

            foreach ($arFilter as $field => $value) {
                $arResult[] = is_array($value) ?
                    sprintf('%s IN (%s)', $this->escapeString($field), $this->escapeString(implode(',', $value))) :
                    $this->escapeString($field) . ' = ' . $this->escapeString($value);
            }

            return $arResult;
        };

        $this->filterQuery = $arFilter['LOGIC_OR'] ?
            implode(' OR ', $buildFilter($arFilter['LOGIC_OR'])) :
            implode(' AND ', $buildFilter($arFilter));

        return $this;
    }

    public function setJoin(string $tableName, string $joinType, array $reference) : self
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

        $this->joinQuery .=
            ' ' . $joinType . ' JOIN ' . $this->escapeString($tableName) .
            ' ON ' .
            $this->escapeString($thisEntity['this_key']) .
            ' = ' .
            $this->escapeString($tableName) .
            '.' .
            $this->escapeString($refEntity['ref_key']);

        return $this;
    }

    public function getQuery() : string
    {
        $query = 'SELECT ' . $this->selectQuery ?: '*' . PHP_EOL;
        $query .= 'WHERE ' . $this->filterQuery . PHP_EOL;
        $query .= 'FROM ' . $this->entity->getTableName() . PHP_EOL;
        $query .= $this->joinQuery ?: '';
        return $query;
    }
}