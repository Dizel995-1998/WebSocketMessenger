<?php

namespace Lib\Database;

use Lib\Container\Container;
use Lib\Database\Interfaces\IConnection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

abstract class DataManager
{
    /**
     * Название колонки для первичного ключа
     * @var string
     */
    protected static string $primaryKeyColumn = '';

    /**
     * Название свойства для первичного ключа
     * @var string
     */
    protected static string $primaryKeyProperty = '';

    /**
     * Мапинг колонок таблицы и свойств обьекта
     * @var array
     */
    protected static array $objectMap = [];

    private static function getConnection() : IConnection
    {
        static $instance = null;
        return $instance ?: $instance = Container::getService(IConnection::class);
    }

    /**
     * Возвращает название таблицы для которой сущность яв-ся отображением
     * @return string
     */
    abstract public static function getTableName() : string;

    /**
     * @param int $id
     * @return static::class
     * @throws ReflectionException
     */
    public static function findByPrimaryKeyOrFail(int $id) : self
    {
        $entity = new static();
        self::getMappingEntity();

        // TODO избавиться от непосредственного вызова билдера тут
        $arDb = self::getConnection()->query(
            (new QueryBuilderSelector(static::getTableName()))
                ->setFilter([self::$primaryKeyColumn => $id])
                ->getQuery())
            ->fetch();

        if (!$arDb) {
            throw new RuntimeException(sprintf('Entity: %s with id %d does not exists', static::class, $id));
        }

        foreach (self::$objectMap as $objectPropertyName => $columnName) {
            if (isset($arDb[$columnName])) {
                $entity->$objectPropertyName = $arDb[$columnName];
                continue;
            }

            throw new RuntimeException(sprintf('Can\'t find column for property %s, entity %s', $objectPropertyName, static::class));
        }

        return $entity;
    }

    /**
     * Сохранение сущности в БД
     * @return bool
     */
    public function save() : bool
    {
        self::getMappingEntity();
        $propPrimaryKey = self::$primaryKeyProperty;

        if ($this->$propPrimaryKey === null) {
            $arUpdate = [];

            foreach (self::$objectMap as $propertyName => $columnName) {
                $arUpdate[$columnName] = $this->$propertyName;
            }

            $query = (new QueryBuilderInserter((static::class)::getTableName()))
                ->insert($arUpdate)
                ->getQuery();

            if ($res = self::getConnection()->exec($query)) {
                $this->$propPrimaryKey = self::getConnection()->getLastInsertId();
            }

            return (bool) $res;
        }

        $builder = new QueryBuilderUpdater((static::class)::getTableName());

        foreach (self::$objectMap as $propertyObjectName => $columnName) {
            $builder->set($columnName, $this->$propertyObjectName);
        }

        $query = $builder->where(self::$primaryKeyColumn, $this->$propPrimaryKey)->getQuery();
        return self::getConnection()->exec($query);
    }

    public function delete()
    {
        self::getMappingEntity();
        // todo поменять тип хранения primaryKey, должнен быть не строкой а массивом, ['название_свойства' => 'название_колонки']
        $query = (new QueryBuilderDeleter((static::class)::getTableName()))
            ->where(self::$primaryKeyColumn, $this->id)
            ->getQuery();

        return self::getConnection()->exec($query);
    }

    /**
     * @param string $column
     * @param string|int|array $value
     * @return static::class
     */
    public static function findByColumnOrFail(string $column, $value) : self
    {
        self::getMappingEntity();
        $entity = new static();
        $query = (new QueryBuilderSelector((static::class)::getTableName()))
            ->setFilter([$column => $value])
            ->getQuery();

        if (!$arDb = self::getConnection()->query($query)->fetch()) {
            throw new RuntimeException(sprintf('Entity: %s with column = %s does not exists', static::class, (string) $value));
        }

        foreach (self::$objectMap as $propertyName => $columnName) {
            if (!array_key_exists($columnName, $arDb)) {
                throw new RuntimeException(sprintf('Table dont have %s column', $columnName));
            }

            $entity->$propertyName = $arDb[$columnName];
        }

        return $entity;
    }

    /**
     * Формирует массив вида propertyName => columnName
     * @return void
     */
    public static function getMappingEntity() : void
    {
        if (self::$objectMap) {
            return;
        }

        self::$primaryKeyColumn = '';
        self::$primaryKeyProperty = '';

        $reflectionClass = new ReflectionClass(static::class);
        $entityProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        if (empty($entityProperties)) {
            throw new RuntimeException('Entity does not have any property');
        }

        foreach ($entityProperties as $property) {
            $phpDocOfProperty = $property->getDocComment();
            $propName = $property->getName();

            if ($primaryKeyColumn = self::findTagInPhpDoc('primary_key', $phpDocOfProperty)) {
                if (self::$primaryKeyColumn) {
                    throw new RuntimeException(sprintf('Duplicate primary key in entity %s', static::class));
                }

                self::$primaryKeyColumn = $primaryKeyColumn;
                self::$primaryKeyProperty = $propName;
            }

            if ($columnName = self::findTagInPhpDoc('column_name', $phpDocOfProperty)) {
                self::$objectMap[$propName] = $columnName;
            }
        }

        if (empty(self::$objectMap)) {
            throw new RuntimeException(sprintf('Entity %s dont have any property', static::class));
        }

        if (!self::$primaryKeyColumn) {
            throw new RuntimeException('Not found primary key');
        }
    }

    /**
     * Поиск служебных данных в phpDoc сущности для работы ОРМ
     * @param string $key
     * @param string $phpDoc
     * @return string|null
     */
    public static function findTagInPhpDoc(string $key, string $phpDoc) : ?string
    {
        preg_match("~@ORM {$key} (?<{$key}>\S+)~", $phpDoc, $matches);
        return $matches[$key];
    }
}