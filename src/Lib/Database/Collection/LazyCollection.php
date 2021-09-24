<?php

namespace Lib\Database\Collection;

use IteratorAggregate;
use Lib\Database\Column\IntegerColumn;
use Lib\Database\Hydrator\Hydrator;
use Lib\Database\MetaData\MetaDataEntity;
use Lib\Database\Property\Property;
use Lib\Database\PropertyMap\PropertyMap;
use Lib\Database\Query\QueryBuilder;
use Lib\Database\Reader\EntityReader;
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


        // Данные полученные якобы от БД
        $arMock = [
            [
                'MIME_TYPE' => 'image/jpeg',
                'PATH' => '/var/www/bitrix/12.jpg',
                'FILE_ID' => 123,
                'EXTENSION' => 'jpeg'
            ],
            [
                'MIME_TYPE' => 'image/jpg',
                'PATH' => '/var/www/bitrix/995.jpg',
                'FILE_ID' => 124,
                'EXTENSION' => 'png'
            ]
        ];

        // Структура которую мы получили из ридера сущностей
        $reader = (new EntityReader())
            ->setEntityMapping([
                new PropertyMap(new Property('path'), new IntegerColumn('PATH')),
                new PropertyMap(new Property('mime_type'), new IntegerColumn('MIME_TYPE')),
                new PropertyMap(new Property('file_id'), new IntegerColumn('FILE_ID')),
                new PropertyMap(new Property('extension'), new IntegerColumn('EXTENSION'))
            ]);

        $arIterable = [];

        foreach ($arMock as $mock) {
            $arIterable[] = Hydrator::getEntity(new MetaDataEntity($this->relation->getTargetClassName(), $reader), new QueryBuilder($mock));
        }

        return new \ArrayIterator($arIterable);
    }
}