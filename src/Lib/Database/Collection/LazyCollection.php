<?php

namespace Lib\Database\Collection;

use IteratorAggregate;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\MetaData\MetaDataEntity;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Relations\BaseRelation;

class LazyCollection implements IteratorAggregate
{
    protected BaseRelation $relation;

    public function __construct(BaseRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getIterator()
    {
        $dataCollection = (new QueryBuilder())->exec();
        $arIterable = [];

        foreach ($dataCollection as $item) {
            $arIterable[] = Hydrator::getEntity(new MetaDataEntity($this->relation->getTargetColumn()->getEntityClassName()), $item);
        }

        return new \ArrayIterator($arIterable);
    }
}