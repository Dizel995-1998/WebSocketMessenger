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

        // TODO вызов событий в более абстрактной сущности

        return self::getConnection()->query($query->getQuery());
    }

    /**
     * @param int $id
     * @return static
     * @throws \ReflectionException
     */
    public static function load(int $id) : self
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
}