<?php

namespace Lib\Database\EntityManager;

use GuzzleHttp\Psr7\Query;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\ReflectionReader;

class EntityManager
{
    public function findByPrimaryKey(string $entityClassName, int|string $id) : ?object
    {
        if (!class_exists($entityClassName)) {
            throw new \InvalidArgumentException(sprintf('Cannot find "%s" class', $entityClassName));
        }

        /** todo забрать тип рефлектора из контейнера */
        $reader = new ReflectionReader($entityClassName);
        $dbData =
            (new QueryBuilder())
            ->select(['*'])
            ->from($reader->getTableName())
            ->where([$reader->getPrimaryKey() => $id])
            ->exec();

        if (!$dbData) {
            return null;
        }

        return Hydrator::getEntity($reader, $dbData);
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
            $arData[$columnName] = $propertyReflector->getValue($entity);
        }

        return (new QueryBuilder())->insert($reader->getTableName(), $arData);
    }
}