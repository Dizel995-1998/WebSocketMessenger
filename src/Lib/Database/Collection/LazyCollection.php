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
        // todo не думаю что запрос должен строится отсюда, должно идти обращение к сервисному слою для работы с БД для построения JOIN запроса
        $sql = sprintf('SELECT * FROM %s JOIN %s ON %s = %s',
            $this->relation->getSourceTable(),
            $this->relation->getTargetTable(),
            $this->relation->getSourceColumn(),
            $this->relation->getTargetColumn()
        );

        $dataCollection = (new QueryBuilder())->exec();
        $arIterable = [];

        foreach ($dataCollection as $item) {
            $arIterable[] = Hydrator::getEntity(new MetaDataEntity($this->relation->getTargetClassName()), $item);
        }

        return new \ArrayIterator($arIterable);
    }
}