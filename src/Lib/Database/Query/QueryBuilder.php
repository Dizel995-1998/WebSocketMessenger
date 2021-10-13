<?php

namespace Lib\Database\Query;

use PDO;

class QueryBuilder
{
    protected string $sql = '';

    protected string $mainTable = '';

    /**
     * Прокинуть тип COnnection
     */
    public function __construct()
    {
    }

    /**
     * @param string[] $fields
     * @return self
     */
    public function select(array $fields) : self
    {
        // fixme: проверка массива на не ассоциативность
        // fixme: экранирование полей
        $this->sql .= sprintf('SELECT %s ', implode(',', $fields));
        return $this;
    }

    public function from(string $tableName) : self
    {
        $this->mainTable = $tableName;
        $this->sql .= sprintf('FROM %s ', $tableName);
        return $this;
    }

    public function join(string $curColumn, string $refColumn, string $targetTable, string $joinType = 'inner') : self
    {
        $allowJoins = ['left', 'right', 'inner'];
        $joinType = mb_strtolower($joinType);

        if (!in_array($joinType, $allowJoins)) {
            throw new \InvalidArgumentException(
                sprintf('joinType must be one of "%s", "%s" given', implode(', ', $allowJoins), $joinType)
            );
        }

        $joinExpression = $this->mainTable . '.' . $curColumn . '=' . $targetTable . '.' . $refColumn;
        $this->sql .= sprintf('%s JOIN %s ON %s ', $joinType, $targetTable, $joinExpression);
        return $this;
    }

    /**
     * @param array-key[] $filter
     * @return $this
     */
    public function where(array $filter) : self
    {
        // fixme: проверка массива на ассоциативность
        // fixme: экранирование полей
        $preparedExpression = '';

        foreach ($filter as $column => $value) {
            $preparedExpression .= $column . (is_array($value) ? ' IN ' : ' = ') . "('$value')";
        }

        $this->sql .= 'WHERE ' . $preparedExpression;
        return $this;
    }

    public function insert(string $tableName, array $fieldsValues)
    {
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $tableName,
            implode(',', array_keys($fieldsValues)),
            implode(',', array_map(function ($item) {
                return is_numeric($item) ? $item : "'$item'";
            }, array_values($fieldsValues)))
        );

        $db = new PDO('mysql:host=mysql;dbname=mydb', 'root', 'root');
        return (bool) $db->exec($sql);
    }

    /**
     * TODO временно возвращает массив, будет возвращать обьект выборки
     * @param bool $multipleRows
     * @return array
     */
    public function exec(bool $multipleRows = false): array
    {
        $db = (new PDO('mysql:host=mysql;dbname=mydb', 'root', 'root'))->query($this->sql);

        if ($multipleRows) {
            return $db->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        return $db->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}