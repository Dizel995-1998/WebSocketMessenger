<?php

namespace Lib\Database\Hydrator;

use Lib\Database\Collection\LazyCollection;
use Lib\Database\Reader\IReader;
use ReflectionClass;

class Hydrator
{
    public static function getEntity(IReader $metaData, array $dbData): object
    {
        $reflectionClass = new ReflectionClass($metaData->getEntityName());
        $ormEntity = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($metaData->getProperties() as $propertyName => $columnName) {
            $propertyReflector = $reflectionClass->getProperty($propertyName);

            /** Если свойство не nullable типа, а в БД нет для него данных */
            if ($propertyReflector->getType()
                && !$propertyReflector->getType()->allowsNull()
                && empty($dbData[$columnName->getName()])
            ) {
                throw new \RuntimeException(sprintf('Trying write null to not nullable property "%s", entity "%s"', $propertyName, $metaData->getEntityName()));
            }

            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$columnName->getName()] ?? null);
        }

        if ($associations = $metaData->getRelations()) {
            foreach ($associations as $propertyName => $relation) {
                $propertyReflector = $reflectionClass->getProperty($propertyName);
                $propertyReflector->setAccessible(true);
                $propertyReflector->setValue($ormEntity, new LazyCollection($relation));
            }
        }

        return $ormEntity;
    }
}