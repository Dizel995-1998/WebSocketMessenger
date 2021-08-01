<?php

namespace Lib\Database;

use RuntimeException;

abstract class DataManager
{
    private static ?\PDO $pdoConnection;

    const DEFAULT_RUNTIME_JOIN_TYPE = 'LEFT';

    private static function getConnection() : \PDO
    {
        $dbHost = 'mysql';
        $dbName = 'mydb';
        $dbUser = 'root';
        $dbPassword = 'root';
        $dsn = "mysql:host={$dbHost};dbname={$dbName}";
        return new \PDO($dsn, $dbUser, $dbPassword);
    }

    abstract public static function getTableName() : string;

    abstract public static function getMap() : array;

    /**
     * @param array $parameters
     * @return ArResult
     */
    public static function getList(array $parameters)
    {
        // TODO сделать свои виды исключений для ORM

        $entityClass = static::class;
        if (!$tableName = $entityClass::getTableName()) {
            throw new \RuntimeException('Method "getTableName" cant return empty string');
        }

        $query = new QueryBuilder($tableName);

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

        // TODO вызов событий

        echo $query->getQuery();
        $obFetch = self::getConnection()->query($query->getQuery());
        return new ArResult($obFetch->fetchAll(\PDO::FETCH_ASSOC));
    }
}