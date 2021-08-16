<?php

namespace Lib\Database;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use Lib\Database\Interfaces\IDbResult;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

// TODO реализовать метод для получения мапы колонок обьекта, чтобы в автоматическом режиме создавать миграции
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
        $query = new QueryBuilderSelector($tableName);

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
     * TODO перейти на пхп 8.0 чтобы избавиться от self в возвращаемом типе, вписать static
     * @param int $id
     * @return static::class
     * @throws \ReflectionException
     */
    public static function findById(int $id) : self
    {
        $entity = new static();
        $reflectionClass = new ReflectionClass(static::class);
        $arProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        if (empty($arProperties)) {
            throw new RuntimeException('Entity does not have any property');
        }

        // todo хардкод
        $QueryBuilderSelector = new QueryBuilderSelector(static::getTableName());
        $QueryBuilderSelector->setFilter(['ID' => $id]);
        $dbConnection = Container::getService(IConnection::class);
        $arDb = $dbConnection->query($QueryBuilderSelector->getQuery())->fetch();

        if (!$arDb) {
            throw new RuntimeException(sprintf('Entity: %s with id %d does not exists', static::class, $id));
        }

        foreach ($arProperties as $property) {
            $phpDocOfProperty = $property->getDocComment();
            $propName = $property->getName();

            // todo ХАРДКОД !!!
            if (preg_match('~@ORM column_name (?<column_name>\S+)~', $phpDocOfProperty, $matches)) {
                if (array_key_exists($matches['column_name'], $arDb)) {
                    $entity->$propName = $arDb[$matches['column_name']];
                    continue;
                }
            }

            if (isset($arDb[$propName])) {
                $entity->$propName = $arDb[$propName];
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

        $dbConn = Container::getService(IConnection::class);

        /** Процедура построения UPDATE запроса todo жёсткий хардкод, должно быть в QueryBuilderSelector */
        $queryBuilderUpdater = new QueryBuilderUpdater((static::class)::getTableName());

        foreach ($arColumnNamePropName as $columnName => $value) {
            $queryBuilderUpdater->set($columnName, $value);
        }

        // TODO хардкод ключа ID
        $queryBuilderUpdater->where('ID', $this->id);

        $query = $queryBuilderUpdater->getQuery();
        return $dbConn->exec($query);
    }

    public function delete()
    {
        // TODO хардкод ключа ID
        $dbConn = Container::getService(IConnection::class);
        $query = (new QueryBuilderDeleter((static::class)::getTableName()))->where('ID', $this->id)->getQuery();
        return $dbConn->exec($query);
    }

    /**
     * @param string $column
     * @param string|int|array $value
     * @return DataManager
     * @throws \ReflectionException
     */
    public static function findByColumnOrFail(string $column, $value) : self
    {
        // TODO дубль кода в этом методе и findById
        $entity = new static();
        $reflectionClass = new ReflectionClass(static::class);
        $arProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        if (empty($arProperties)) {
            throw new RuntimeException('Entity does not have any property');
        }

        // todo хардкод
        $queryBuilder = new QueryBuilderSelector((static::class)::getTableName());
        $queryBuilder->setFilter([$column => $value]);

        $dbConnection = Container::getService(IConnection::class);
        $query = $queryBuilder->getQuery();
        $arDb = $dbConnection->query($query)->fetch();

        if (!$arDb) {
            throw new RuntimeException(sprintf('Entity: %s with column = %s does not exists', static::class, (string) $value));
        }

        foreach ($arProperties as $property) {
            $phpDocOfProperty = $property->getDocComment();
            $propName = $property->getName();

            // todo ХАРДКОД !!!
            if (preg_match('~@ORM column_name (?<column_name>\S+)~', $phpDocOfProperty, $matches)) {
                if (array_key_exists($matches['column_name'], $arDb)) {
                    $entity->$propName = $arDb[$matches['column_name']];
                    continue;
                }
            }

            if (isset($arDb[$propName])) {
                $entity->$propName = $arDb[$propName];
                continue;
            }

            throw new RuntimeException(sprintf('Can\'t find column for property %s, entity %s', $propName, static::class));
        }

        return $entity;
    }
}