<?php

namespace Lib\Database;

class DataManager
{
    private static DecoratorConnection $connection;

    public static function getList(EntityTable $entity, array $parameters)
    {
        $query = new QueryBuilder($entity);

        $query->setSelect($parameters['select']);
        $query->setFilter($parameters['filter']);

        // TODO вызов событий

        return self::$connection->execQuery($query->getQuery());
    }

    public static function add(EntityTable $entity, array $fields)
    {
    }

    public static function update(EntityTable $entity, array $fields)
    {

    }

    public static function delete(EntityTable $entityTable, array $rowsIds)
    {

    }
}