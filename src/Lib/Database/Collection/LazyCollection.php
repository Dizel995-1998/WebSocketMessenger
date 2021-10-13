<?php

namespace Lib\Database\Collection;

use IteratorAggregate;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\ArrayReader;
use Lib\Database\Reader\ReflectionReader;
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
        $dataCollection = (new QueryBuilder())
            ->select(['*'])
            ->from($this->relation->getSourceTable())
            ->join($this->relation->getSourceColumn(), $this->relation->getTargetColumn(), $this->relation->getTargetTable())
            ->where([$this->relation->getSourceTable() . '.ID' => 2]) // fixme: в отношениях не продумана логика условий where
            ->exec(true);

        $arIterable = [];

        foreach ($dataCollection as $item) {
            $arIterable[] = Hydrator::getEntity(new ReflectionReader($this->relation->getTargetEntity()), $item);
        }

        return new \ArrayIterator($arIterable);
    }
}