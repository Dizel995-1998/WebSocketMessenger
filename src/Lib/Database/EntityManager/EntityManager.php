<?php

namespace Lib\Database\EntityManager;

use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\IReader;
use Lib\Database\Reader\ReflectionReader;

class EntityManager
{
    /** @var array Коллекция восстановленных из БД объектов */
    protected static array $unitOfWork = [];

    protected IReader $entityReader;

    public function __construct(IReader $entityReader)
    {
        $this->entityReader = $entityReader;
    }

    public function findBy(string $entityClassName, string $field, int|string $identify) : ?object
    {
        if (!class_exists($entityClassName)) {
            throw new \InvalidArgumentException(sprintf('Cannot find "%s" class', $entityClassName));
        }

        /** todo забрать тип рефлектора из контейнера */
        $reader = new ReflectionReader($entityClassName);

        $whereColumn = $reader->getColumnNameByProperty($field);

        $dbData =
            (new QueryBuilder())
                ->select(['*'])
                ->from($reader->getTableName())
                ->where([$whereColumn => $identify])
                ->exec();

        if (!$dbData) {
            return null;
        }

        $entity = Hydrator::getEntity($reader, $dbData);

        self::$unitOfWork[spl_object_hash($entity)] = $entity;
        return $entity;
    }

    public function findByPrimaryKey(string $entityClassName, int|string $id) : ?object
    {
        return $this->findBy($entityClassName, $this->entityReader->getPrimaryKey(), $id);
    }

    public function save(object $entity)
    {
        $reader = new ReflectionReader(get_class($entity));
        $arData = [];

        foreach ($reader->getProperties() as $propertyName => $columnName) {
            // fixme: hardcode
            if ($propertyName == 'id') {
                continue;
            }

            $propertyReflector = new \ReflectionProperty($entity, $propertyName);
            $propertyReflector->setAccessible(true);

            // todo: в будущем так же сделать проверку на nullable из ридера
            if (
                !$propertyReflector->hasDefaultValue() &&
                !$propertyReflector->isInitialized($entity)
            ) {
                throw new \RuntimeException(sprintf('Сущность "%s" имеет не инициализированное свойство: "%s"', get_class($entity), $propertyName));
            }

            $arData[$columnName] = $propertyReflector->getValue($entity);
        }

        // fixme: hardCode getId method, must be part of interface
        if (isset(self::$unitOfWork[spl_object_hash($entity)])) {
            return (new QueryBuilder())->update($reader->getTableName(), $arData, [$reader->getPrimaryKey() => $entity->getId()]);
        }

        // fixme: возвращать сущности её идентификатор - id
        return (new QueryBuilder())->insert($reader->getTableName(), $arData);
    }
}