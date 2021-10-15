<?php

namespace Lib\Database\Collection;

use IteratorAggregate;
use Lib\Container\Container;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\ArrayReader;
use Lib\Database\Reader\ReflectionReader;
use Lib\Database\Relations\BaseRelation;

class LazyCollection implements IteratorAggregate
{
    /**
     * Получаем обьект отношения, чтобы в getIterator построить SELECT запрос на выборку связанной сущности
     * @param BaseRelation $relation
     */
    public function __construct(protected BaseRelation $relation) {}

    public function getIterator()
    {
        /**
         * @var QueryBuilder
         */
        $queryBuilder = Container::getService(QueryBuilder::class);

        $dataCollection = $queryBuilder
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