<?php

namespace Lib\Database\Query;

use Lib\Database\Drivers\Interfaces\IConnection;

/**
 * fixme: необходимо экранирование
 * todo: необходим интерфейс
 */
class QueryBuilder
{
    protected string $sql = '';

    protected string $mainTable = '';

    /**
     * @param IConnection $dbConnection
     */
    public function __construct(protected IConnection $dbConnection) { }

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
            $preparedExpression .= ' AND ' . $column . (is_array($value) ? ' IN ' : ' = ') . "('$value')";
        }

        $this->sql .= 'WHERE 1=1 ' . $preparedExpression;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset) : self
    {
        $this->sql .= ' OFFSET = ' . $offset;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return $this
     */
    public function limit(?int $limit = null) : self
    {
        $limit && $this->sql .= ' LIMIT ' . $limit;
        return $this;
    }

    protected function escapeExpression($expression) : int|string
    {
        return is_numeric($expression) ? $expression : "'$expression'";
    }

    protected function prepareFields(array $fields) : string
    {
        return implode(',', array_keys($fields));
    }

    protected function prepareValues(array $values) : string
    {
        return implode(',', array_map(function ($item) {
            return $this->escapeExpression($item);
        }, array_values($values)));
    }

    public function update(string $tableName, array $fieldsValues, array $whereExpression) : bool
    {

        $sql = "UPDATE $tableName SET ";

        foreach ($fieldsValues as $field => $value) {
            $sql .= $field . ' = ' . $this->escapeExpression($value) . ',';
        }

        // fixme: костыль
        $sql = substr($sql, 0, -1);

        if ($whereExpression) {
            $sql .= ' WHERE ';

            foreach ($whereExpression as $field => $value) {
                $sql .= $field . ' = ' . $this->escapeExpression($value) . ',';
            }

            // fixme: костыль
            $sql = substr($sql, 0, -1);
        }

        return $this->dbConnection->exec($sql);
    }

    /**
     * Вставляет запись в СУБД
     * @param string $tableName
     * @param array $fieldsValues
     * @return string Возвращает идентификатор последней вставленной записи
     */
    public function insert(string $tableName, array $fieldsValues) : string
    {
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $tableName,
            $this->prepareFields($fieldsValues),
            $this->prepareValues($fieldsValues)
        );

        $this->dbConnection->exec($sql);
        return (string) $this->dbConnection->getLastInsertedId();
    }

    /**
     * @param bool $multipleRows
     * @return array
     */
    public function exec(bool $multipleRows = false): array
    {
        $dbRes = $this->dbConnection->query($this->sql);
        $this->sql = '';

        return $multipleRows ?
            $dbRes->fetchAll() :
            $dbRes->fetch();
    }
}