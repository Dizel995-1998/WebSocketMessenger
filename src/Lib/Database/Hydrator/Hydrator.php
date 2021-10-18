<?php

namespace Lib\Database\Hydrator;

use Lib\Container\Container;
use Lib\Database\Collection\LazyCollection;
use Lib\Database\Collection\Proxy;
use Lib\Database\EntityManager\EntityManager;
use Lib\Database\Reader\IReader;
use Lib\Database\Reader\ReflectionReader\ReflectionReader;
use Lib\Database\Relations\OneToOne;
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

                if ($relation instanceof OneToOne) {
                    $propName = $metaData->getPropertyNameByColumn($relation->getSourceColumn());
                    $refProp = $reflectionClass->getProperty($propName);
                    $refProp->setAccessible(true);
                    $conditionValue = $refProp->getValue($ormEntity);

                    // fixme: хардкод рефлектор ридера
                    // fixme: жёсткий говнокод
                    $targetEntityClassName = ReflectionReader::getEntityClassNameByTable($relation->getTargetTable());
                    $entityManager = \Lib\Container\Container::getService(EntityManager::class);
                    $reader = Container::getService(IReader::class);
                    $reader->readEntity($targetEntityClassName);


                    $proxy = (new Proxy($targetEntityClassName, $entityManager));
                    $proxy->onBeforeCallAnyMethod(function ($whereCondition) {
                        return $this->entityManager->findOrFailBy($this->originClassName, $whereCondition);
                    }, [$reader->getPrimaryProperty() => $conditionValue]);

                    $propertyReflector->setValue($ormEntity, $proxy);
                    continue;
                }

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