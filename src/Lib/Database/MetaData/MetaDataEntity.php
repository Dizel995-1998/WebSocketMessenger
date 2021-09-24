<?php

namespace Lib\Database\MetaData;

use Lib\Database\Reader\EntityReader;
use Lib\Database\Relations\BaseRelation;

class MetaDataEntity
{
    protected string $className;
    protected EntityReader $reader;

    /**
     * @param string $className
     * @param EntityReader $reader
     */
    public function __construct(string $className, EntityReader $reader)
    {
        $this->className = $className;
        $this->reader = $reader;
    }

    /**
     * Возвращает название класса сущности
     * @return string
     */
    public function getSourceClassName(): string
    {
        return $this->className;
    }

    public function getColumns(): array
    {

    }

    public function getProperties(): array
    {

    }

    /**
     * @return <string, BaseRelation>[]
     */
    public function getRelations(): array
    {
        return $this->reader->getEntityRelations();
    }

    public function getMapping(): array
    {
        return $this->reader->getEntityMapping();
    }
}