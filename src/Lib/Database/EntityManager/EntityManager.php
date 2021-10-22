<?php

namespace Lib\Database\EntityManager;

use Entity\AccessToken;
use Entity\Message;
use Entity\Picture;
use Entity\User;
use InvalidArgumentException;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\IReader;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

class EntityManager
{
    /** @var array Коллекция восстановленных из БД объектов */
    protected static array $unitOfWork = [];

    public function __construct(
        protected IReader $entityReader,
        protected QueryBuilder $queryBuilder
    ) {
        $this->entityReader->loadOrmClasses(...[
            User::class,
            AccessToken::class,
            Message::class,
            Picture::class
        ]);
    }

    public function findOrFailBy(string $entityClassName, array $whereCondition) : object
    {
        if (!$object = $this->findBy($entityClassName, $whereCondition)) {
            $filter = '';

            foreach ($whereCondition as $filterKey => $filterValue) {
                $filter .=  ' ' . $filterKey . ' = ' . $filterValue;
            }

            throw new RuntimeException(sprintf('Cannot find %s, with condition %s', $entityClassName, $filter));
        }

        return $object;
    }

    public function findBy(string $entityClassName, array $whereCondition, ?int $limit = null) : ?object
    {
        if (!class_exists($entityClassName)) {
            throw new InvalidArgumentException(sprintf('Cannot find "%s" class', $entityClassName));
        }

        /** Конвертация ключей свойств в ключи колонки **/
        $preparedCondition = [];

        foreach ($whereCondition as $propName => $propValue) {
            if (!($column = $this->entityReader->getColumnNameByProperty($entityClassName, $propName))) {
                throw new InvalidArgumentException(sprintf('Cannot find column for prop %s', $propName));
            }

            $preparedCondition[$column->getName()] = $propValue;
        }

        $dbData =
            $this->queryBuilder
                ->select(['*'])
                ->from($this->entityReader->getTableNameByEntity($entityClassName))
                ->where($preparedCondition)
                ->limit($limit)
                ->exec();

        if (!$dbData) {
            return null;
        }

        $entity = Hydrator::getEntity($entityClassName, $this->entityReader, $dbData);

        if ($this->isFilled($entity)) {
            self::$unitOfWork[spl_object_hash($entity)] = $entity;
        }

        return $entity;
    }

    public function findByPrimaryKey(string $entityClassName, int|string $id) : object
    {
        if (!$entity = $this->findBy($entityClassName, [$this->entityReader->getPkPropertyByEntity($entityClassName) => $id])) {
            throw new RuntimeException(sprintf('Cannot find "%s" with primary key %d', $entityClassName, $id));
        }

        return $entity;
    }

    /**
     * Проверяет заполненно ли хоть одно свойство объекта
     * @param object $checkObj
     * @return bool
     */
    protected function isFilled(object $checkObj) : bool
    {
        return (bool) array_filter((array) $checkObj);
    }

    /**
     * todo: добавить валидации входного объекта
     * @throws ReflectionException
     */
    public function save(object $entity) : bool
    {
        $this->entityReader->readEntity(get_class($entity));
        $arData = [];

        foreach ($this->entityReader->getProperties() as $propertyName => $column) {
            // todo: В данной реализации PK может быть только автоинкрементом, нужно ввести новый параметр в BaseColumn, isAutoIncrement
            if ($column->isPrimaryKey()) {
                continue;
            }

            $propertyReflector = new ReflectionProperty($entity, $propertyName);
            $propertyReflector->setAccessible(true);

            // todo: в будущем так же сделать проверку на nullable из ридера
            if (
                !$propertyReflector->hasDefaultValue() &&
                !$propertyReflector->isInitialized($entity)
            ) {
                throw new RuntimeException(sprintf('Сущность "%s" имеет не инициализированное свойство: "%s"', get_class($entity), $propertyName));
            }

            if ($propValue = $propertyReflector->getValue($entity)) {
                $arData[$column->getName()] = $propValue;
            }
        }

        /** Если это запись "поднятая" из БД, производим UPDATE операцию */
        if (isset(self::$unitOfWork[spl_object_hash($entity)])) {
            $propPk = new ReflectionProperty($entity, $this->entityReader->getPrimaryProperty());
            $propPk->setAccessible(true);

            return $this->queryBuilder->update(
                $this->entityReader->getTableName(),
                $arData,
                [$this->entityReader->getPrimaryColumn() => $propPk->getValue($entity)]
            );
        }

        // Присвоение идентификатора БД - сущности
        $id = $this->queryBuilder->insert($this->entityReader->getTableName(), $arData);

        $propertyReflector = new ReflectionProperty($entity, $this->entityReader->getPrimaryProperty());
        $propertyReflector->setAccessible(true);
        $propertyReflector->setValue($entity, $id);
        return true;
    }
}