<?php

namespace Lib\Database\Hydrator;

use Lib\Database\Collection\LazyCollection;
use Lib\Database\MetaData\MetaDataEntity;
use ReflectionClass;

class Hydrator
{
    public static function getEntity(MetaDataEntity $metaData, array $dbData): object
    {
        $reflectionClass = new ReflectionClass($metaData->getSourceClassName());
        $ormEntity = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($metaData->getMapping() as $propertyMap) {
            $propertyReflector = $reflectionClass->getProperty($propertyMap->getProperty()->getName());
            $propertyReflector->setAccessible(true);
            $propertyReflector->setValue($ormEntity, $dbData[$propertyMap->getColumn()->getName()]);
        }

        if ($associations = $metaData->getRelations()) {
            foreach ($associations as $propertyName => $association) {
                $propertyReflector = $reflectionClass->getProperty($propertyName);
                $propertyReflector->setAccessible(true);
                $propertyReflector->setValue($ormEntity, new LazyCollection($association));
            }
        }

        return $ormEntity;
    }
}