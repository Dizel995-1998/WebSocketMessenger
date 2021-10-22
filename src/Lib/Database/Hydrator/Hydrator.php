<?php

namespace Lib\Database\Hydrator;

use Lib\Container\Container;
use Lib\Database\Collection\DataBaseProxy;
use Lib\Database\Collection\LazyCollection;
use Lib\Database\EntityManager\EntityManager;
use Lib\Database\Reader\IReader;
use Lib\Database\Relations\OneToOne;
use ReflectionClass;
use RuntimeException;

class Hydrator
{
    public static function getEntity(string $entityClass, IReader $metaData, array $dbData): object
    {
        $reflectionClass = new ReflectionClass($entityClass);
        $ormEntity = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($metaData->getColumns($entityClass) as $propertyName => $column) {
            $propertyReflector = $reflectionClass->getProperty($propertyName);

            /** Если свойство не nullable типа, а в БД нет для него данных */
            if ($propertyReflector->getType()
                && !$propertyReflector->getType()->allowsNull()
                && empty($dbData[$column->getName()])
            ) {
                throw new RuntimeException(sprintf('Trying write null to not nullable property "%s", entity "%s"', $propertyName, $entityClass));
            }

            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$column->getName()] ?? null);
        }

        if ($associations = $metaData->getRelations($entityClass)) {
            foreach ($associations as $propertyName => $relation) {
                $propertyReflector = $reflectionClass->getProperty($propertyName);
                $propertyReflector->setAccessible(true);

                if ($relation instanceof OneToOne) {
                    if (!$propName = $metaData->getPropertyNameByColumn($entityClass, $relation->getSourceColumn())) {
                        throw new RuntimeException(sprintf('Не могу получить название свойства по колонке'));
                    }

                    /** Если у владельца связи нет связанной сущности */
                    if (!$foreignKeyValue = $metaData->getReflectionProperty($entityClass, $propName)->getValue($ormEntity)) {
                        continue;
                    }

                    $targetEntityClassName = $metaData->getEntityClassNameByTable($relation->getTargetTable());
                    $propertyReflector->setValue($ormEntity, (new DataBaseProxy($targetEntityClassName, $foreignKeyValue)));
                    continue;
                }

                // Для выборке только по нашей сущности WHERE ...
                $propPrimaryKey = $reflectionClass->getProperty($metaData->getPkColumnByEntity($entityClass));
                $propPrimaryKey->setAccessible(true);
                $columnExpression = $metaData->getPkColumnByEntity($entityClass);

                $whereCondition = [$metaData->getTableNameByEntity($entityClass) . '.' . $columnExpression => $propPrimaryKey->getValue($ormEntity)];
                $propertyReflector->setValue($ormEntity, new LazyCollection($relation, $metaData, $whereCondition));
            }
        }

        return $ormEntity;
    }
}