<?php

namespace Lib\Database\EntityManager;

use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\IReader;

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

        $this->entityReader->readEntity($entityClassName);
        $whereColumn = $this->entityReader->getColumnNameByProperty($field);

        $dbData =
            (new QueryBuilder())
                ->select(['*'])
                ->from($this->entityReader->getTableName())
                ->where([$whereColumn => $identify])
                ->exec();

        if (!$dbData) {
            return null;
        }

        $entity = Hydrator::getEntity($this->entityReader, $dbData);

        self::$unitOfWork[spl_object_hash($entity)] = $entity;
        return $entity;
    }

    public function findByPrimaryKey(string $entityClassName, int|string $id) : ?object
    {
        return $this->findBy($entityClassName, $this->entityReader->getPrimaryKey(), $id);
    }

    /**
     * @throws \ReflectionException
     */
    public function save(object $entity)
    {
        $this->entityReader->readEntity(get_class($entity));
        $arData = [];

        foreach ($this->entityReader->getProperties() as $propertyName => $columnName) {
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
            return (new QueryBuilder())->update($this->entityReader->getTableName(), $arData, [$this->entityReader->getPrimaryKey() => $entity->getId()]);
        }

        // Присвоение идентификатора БД - сущности
        $id = (new QueryBuilder())->insert($this->entityReader->getTableName(), $arData);

        $propertyReflector = new \ReflectionProperty($entity, $this->entityReader->getPrimaryProperty());
        $propertyReflector->setAccessible(true);
        $propertyReflector->setValue($entity, $id);
        return true;
    }
}