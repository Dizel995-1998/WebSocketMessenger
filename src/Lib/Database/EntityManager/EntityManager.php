<?php

namespace Lib\Database\EntityManager;

use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\IReader;

class EntityManager
{
    /** @var array Коллекция восстановленных из БД объектов */
    protected static array $unitOfWork = [];


    public function __construct(
        protected IReader $entityReader,
        protected QueryBuilder $queryBuilder
    ) {

    }

    public function findBy(string $entityClassName, array $whereCondition) : ?object
    {
        if (!class_exists($entityClassName)) {
            throw new \InvalidArgumentException(sprintf('Cannot find "%s" class', $entityClassName));
        }

        $this->entityReader->readEntity($entityClassName);

        /** Выражение с уже подставленными колонками вместо названий свойств **/
        $preparedCondition = [];

        foreach ($whereCondition as $propName => $propValue) {
            $preparedCondition[$this->entityReader->getColumnNameByProperty($propName)] = $propValue;
        }

        $dbData =
            $this->queryBuilder
                ->select(['*'])
                ->from($this->entityReader->getTableName())
                ->where($preparedCondition)
                ->exec();

        if (!$dbData) {
            return null;
        }

        $entity = Hydrator::getEntity($this->entityReader, $dbData);

        if (isset($entity)) {
            self::$unitOfWork[spl_object_hash($entity)] = $entity;
        }

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

            if ($propValue = $propertyReflector->getValue($entity)) {
                $arData[$columnName] = $propValue;
            }
        }

        // fixme: необходимо обращение к колонке с первичным ключом!!!
        if (isset(self::$unitOfWork[spl_object_hash($entity)])) {
            return $this->queryBuilder->update($this->entityReader->getTableName(), $arData, [$this->entityReader->getPrimaryKey() => $entity->getId()]);
        }

        // Присвоение идентификатора БД - сущности
        $id = $this->queryBuilder->insert($this->entityReader->getTableName(), $arData);

        $propertyReflector = new \ReflectionProperty($entity, $this->entityReader->getPrimaryProperty());
        $propertyReflector->setAccessible(true);
        $propertyReflector->setValue($entity, $id);
        return true;
    }
}