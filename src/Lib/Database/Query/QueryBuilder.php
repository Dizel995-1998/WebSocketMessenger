<?php

namespace Lib\Database\Query;

use PDO;

class QueryBuilder
{
    protected string $sql = '';

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
        $this->sql .= sprintf('FROM %s ', $tableName);
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

    /**
     * TODO временно возвращает массив, будет возвращать обьект выборки
     * @return array
     */
    public function exec(): array
    {
        $db = new PDO('mysql:host=mysql;dbname=mydb', 'root', 'root');
        return $db->query($this->sql)->fetch(PDO::FETCH_ASSOC);
    }
}