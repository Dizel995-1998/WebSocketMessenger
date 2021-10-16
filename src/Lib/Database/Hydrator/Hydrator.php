<?php

namespace Lib\Database\Hydrator;

use Lib\Database\Collection\LazyCollection;
use Lib\Database\Reader\IReader;
use ReflectionClass;
use RuntimeException;

class Hydrator
{
    public static function getEntity(IReader $metaData, array $dbData): object
    {
        $reflectionClass = new ReflectionClass($metaData->getEntityName());
        $ormEntity = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($metaData->getProperties() as $propertyName => $column) {
            $propertyReflector = $reflectionClass->getProperty($propertyName);

            /** Если свойство не nullable типа, а в БД нет для него данных */
            if ($propertyReflector->getType()
                && !$propertyReflector->getType()->allowsNull()
                && empty($dbData[$column->getName()])
            ) {
                throw new RuntimeException(sprintf('Trying write null to not nullable property "%s", entity "%s"', $propertyName, $metaData->getEntityName()));
            }

            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$column->getName()] ?? null);
        }

        if ($associations = $metaData->getRelations()) {
            foreach ($associations as $propertyName => $relation) {
                $propertyReflector = $reflectionClass->getProperty($propertyName);
                $propertyReflector->setAccessible(true);

                // Для выборке только по нашей сущности WHERE ...
                $propPrimaryKey = $reflectionClass->getProperty($metaData->getPrimaryProperty());
                $propPrimaryKey->setAccessible(true);
                $columnExpression = $metaData->getColumnNameByProperty($metaData->getPrimaryProperty())->getName();

                $whereCondition = [$metaData->getTableName() . '.' . $columnExpression => $propPrimaryKey->getValue($ormEntity)];
                $propertyReflector->setValue($ormEntity, new LazyCollection($relation, $whereCondition));
            }
        }

        return $ormEntity;
    }
}