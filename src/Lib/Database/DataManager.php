<?php

namespace Lib\Database;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Database\Interfaces\IDbResult;
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

    abstract public static function getMap() : array;

    /**
     * @param array $parameters
     * @return IDbResult
     */
    public static function getList(array $parameters) : IDbResult
    {
        // TODO сделать свои виды исключений для ORM

        $entityClass = static::class;
        if (!$tableName = $entityClass::getTableName()) {
            throw new \RuntimeException('Method "getTableName" can\'t return empty string');
        }

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
}