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
    protected bool $initialized = false;
    protected array $elements = [];

    /**
     * Получаем обьект отношения, чтобы в getIterator построить SELECT запрос на выборку связанной сущности
     * @param BaseRelation $relation
     * @param array $where
     */
    public function __construct(
        protected BaseRelation $relation,
        protected array $where = []
    ) {

    }

    public function getAll() : array
    {
        if (!$this->initialized) {
            $queryBuilder = Container::getService(QueryBuilder::class);
            $dataCollection = $queryBuilder
                ->select(['*'])
                ->from($this->relation->getSourceTable())
                ->join($this->relation->getSourceColumn(), $this->relation->getTargetColumn(), $this->relation->getTargetTable())
                ->where($this->where)
                ->exec(true);

            foreach ($dataCollection as $item) {
                // fixme: какое то говно, придумать красивый механизм получения названия сущности по таблице
                $reader = Container::getService(IReader::class);
                $entityRelationName = $reader->getEntityClassNameByTable($this->relation->getTargetTable());
                $reader->readEntity($entityRelationName);

                $this->elements[] = Hydrator::getEntity($reader, $item);
            }

            $this->initialized = true;
        }

        return $this->elements;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }
}