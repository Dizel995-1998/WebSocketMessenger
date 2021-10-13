<?php

namespace Lib\Database\Collection;

use IteratorAggregate;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\ArrayReader;
use Lib\Database\Relations\BaseRelation;

class LazyCollection implements IteratorAggregate
{
    protected BaseRelation $relation;

    /**
     * Получаем обьект отношения, чтобы в getIterator построить SELECT запрос на выборку связанной сущности
     * @param BaseRelation $relation
     */
    public function __construct(BaseRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getIterator()
    {
        $dataCollection = (new QueryBuilder([
            [
                'FILE_ID' => 566,
                'FILE_EXTENSION' => 'jpg'
            ]
        ]))->exec();
        $arIterable = [];

        foreach ($dataCollection as $item) {
            $arIterable[] = Hydrator::getEntity(new ArrayReader([
                'mapping' => [
                    'file_id' => 'FILE_ID',
                    'extension' => 'FILE_EXTENSION'
                ],
                'entity_name' => \Picture::class,
                'table_name' => 'users'
            ]), $item);
        }

        return new \ArrayIterator($arIterable);
    }
}