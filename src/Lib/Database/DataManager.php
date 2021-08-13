<?php

namespace Lib\Database;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Database\Interfaces\IDbResult;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

abstract class DataManager
{
    private static ?\PDO $pdoConnection;

    const DEFAULT_RUNTIME_JOIN_TYPE = 'LEFT';

    private static function getConnection() : IConnection
    {
        return Container::getService(IConnection::class);
    }

    abstract public static function getTableName() : string;

    /**
     * @param array $parameters
     * @return IDbResult
     */
    public static function getList(array $parameters) : IDbResult
    {
        // TODO сделать свои виды исключений для ORM

        if (!$tableName = (static::class)::getTableName()) {
            throw new \RuntimeException('Method "getTableName" can\'t return empty string');
        }

        // todo хардкод
        $query = new QueryBuilder($tableName);

        // TODO тут необходима проверка, если в селекте не указано не одно из полей, то необходимо сформировать дефолтные алиасы для связанных сущностей

        $parameters['select'] && $query->setSelect($parameters['select']);
        $parameters['filter'] && $query->setFilter($parameters['filter']);

        if (isset($parameters['runtime'])) {
            foreach ($parameters['runtime'] as $runtimeTableAlias => $runtimeEntity) {
                if (!$runtimeEntity['reference']) {
                    throw new \InvalidArgumentException('Missing required field "reference" in runtime');
                }

                if (!is_array($runtimeEntity['reference'])) {
                    throw new \InvalidArgumentException(sprintf('Field "reference" must have type of array, %s given', gettype($runtimeEntity['reference'])));
                }

                if (!$runtimeEntity['data_type']) {
                    throw new \InvalidArgumentException('Missing required field "data_type" in runtime');
                }

                $runtimeEntity['join_type'] = $runtimeEntity['join_type'] ?: self::DEFAULT_RUNTIME_JOIN_TYPE;

                if (!(new $runtimeEntity['data_type']) instanceof DataManager) {
                    throw new RuntimeException('Runtime entity must have parent DataManager');
                }

                $runtimeTable = $runtimeEntity['data_type']::getTableName();
                $query->setJoin($runtimeTable, $runtimeTableAlias, $runtimeEntity['join_type'], $runtimeEntity['reference']);
            }
        }

        // TODO вызов событий в более абстрактной сущности

        return self::getConnection()->query($query->getQuery());
    }

    /**
     * @param int $id
     * @return static::class
     * @throws \ReflectionException
     */
    public static function findById(int $id) : ?self
    {
        $entity = new static();
        $reflectionClass = new ReflectionClass(static::class);
        $arProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        if (empty($arProperties)) {
            throw new RuntimeException('Entity does not have any property');
        }

        // todo хардкод
        $queryBuilder = new QueryBuilder(static::getTableName());
        $queryBuilder->setFilter(['ID' => $id]);
        $dbConnection = Container::getService(IConnection::class);
        $arDb = $dbConnection->query($queryBuilder->getQuery())->fetch();

        if (!$arDb) {
            return null;
            //throw new RuntimeException(sprintf('Entity: %s with id %d does not exists', static::class, $id));
        }

        foreach ($arProperties as $property) {
            $phpDocOfProperty = $property->getDocComment();
            $propName = $property->getName();

            // todo ХАРДКОД !!!
            if (preg_match('~@ORM column_name (?<column_name>\S+)~', $phpDocOfProperty, $matches)) {
                if ($value = $arDb[$matches['column_name']]) {
                    $entity->$propName = $value;
                    continue;
                }
            }

            if ($value = $arDb[$propName]) {
                $entity->$propName = $value;
                continue;
            }

            throw new RuntimeException(sprintf('Can\'t find column for property %s, entity %s', $propName, static::class));
        }

        return $entity;
    }

    public function save() : bool
    {
        // TODO дубль кода
        $reflectionClass = new ReflectionClass(static::class);
        $arProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $arColumnNamePropName = [];

        foreach ($arProperties as $property) {
            $phpDocOfProperty = $property->getDocComment();

            // todo ХАРДКОД !!!
            if (preg_match('~@ORM column_name (?<column_name>\S+)~', $phpDocOfProperty, $matches)) {
                $arColumnNamePropName[$matches['column_name']] = $property->getValue($this);
                continue;
            }

            $arColumnNamePropName[$property->getName()] = $property->getValue($this);
        }

        if (!$arColumnNamePropName) {
            throw new RuntimeException('There is no properties for update');
        }

        $arUpdatesFileds = [];
        $dbConn = Container::getService(IConnection::class);

        /** Процедура построения UPDATE запроса todo жёсткий хардкод, должно быть в queryBUilder */
        foreach ($arColumnNamePropName as $columnName => $value) {
            $arUpdatesFileds[] = $columnName . ' = ' . (is_numeric($value) ? $value : $dbConn->quote($value));
        }

        $query = sprintf('UPDATE %s SET %s WHERE id = %d', (static::class)::getTableName(), implode(',', $arUpdatesFileds), $this->id);
        return $dbConn->exec($query);
    }

    public function delete()
    {
        $dbConn = Container::getService(IConnection::class);
        $query = sprintf('DELETE FROM %s WHERE id = %d', (static::class)::getTableName(), $this->id);
        return $dbConn->exec($query);
    }
}