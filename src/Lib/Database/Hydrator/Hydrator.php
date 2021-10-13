<?php

namespace Lib\Database\Hydrator;

use Lib\Database\Collection\LazyCollection;
use Lib\Database\MetaData\MetaDataEntity;
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
            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$columnName]);
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