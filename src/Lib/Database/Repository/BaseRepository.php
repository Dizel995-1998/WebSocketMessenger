<?php

namespace Lib\Database\Repository;

use Lib\Database\Hydrator\Hydrator;
use Lib\Database\MetaData\MetaDataEntity;
use Lib\Database\Query\QueryBuilder;

abstract class BaseRepository
{
    /** @var string TODO временныый хардкод */
    const PRIMARY_KEY_FIELD = 'ID';

    abstract public static function getClassNameEntity() : string;

    public static function find(int $id): object
    {
        $dbData = (new QueryBuilder())
            ->select([])
            ->filter([self::PRIMARY_KEY_FIELD => $id])
            ->exec();

        return Hydrator::getEntity(new MetaDataEntity(static::class::getClassNameEntity()), $dbData);
    }
}