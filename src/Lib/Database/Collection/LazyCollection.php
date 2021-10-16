<?php

namespace Lib\Database\Collection;

use IteratorAggregate;
use Lib\Container\Container;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\ArrayReader;
use Lib\Database\Reader\IReader;
use Lib\Database\Reader\ReflectionReader;
use Lib\Database\Relations\BaseRelation;

class LazyCollection implements IteratorAggregate
{
    /**
     * Получаем обьект отношения, чтобы в getIterator построить SELECT запрос на выборку связанной сущности
     * @param BaseRelation $relation
     */
    public function __construct(
        protected BaseRelation $relation,
        protected array $where = []
    ) {

    }

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
            ->where($this->where)
            ->exec(true);

        $arIterable = [];

        foreach ($dataCollection as $item) {
            $reader = Container::getService(IReader::class);
            $entityRelationName = $reader::class::getEntityClassNameByTable($this->relation->getTargetTable());
            $reader->readEntity($entityRelationName);
            $arIterable[] = Hydrator::getEntity($reader, $item);
        }

        return new \ArrayIterator($arIterable);
    }
}