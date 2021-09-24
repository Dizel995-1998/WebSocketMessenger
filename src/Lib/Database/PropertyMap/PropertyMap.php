<?php

namespace Lib\Database\PropertyMap;

use Lib\Database\Column\BaseColumn;
use Lib\Database\Property\Property;
use Lib\Database\Relations\BaseRelation;

/**
 * TODO может стоить реализовать отдельный класс для свойств? class Property
 */
class PropertyMap
{
    protected Property $property;

    protected ?BaseColumn $column = null;

    protected ?BaseRelation $relation = null;

    public function __construct(Property $property, BaseColumn $column = null)
    {
        $this->property = $property;
        $this->column = $column;
    }

    public function setRelation(BaseRelation $relation)
    {
        $this->relation = $relation;
    }

    public function getRelation(): BaseRelation
    {
        return $this->relation;
    }

    public function isRelation(): bool
    {
        return isset($this->relation);
    }

    public function getPropertyName(): string
    {
        return $this->property->getName();
    }

    public function getColumn(): BaseColumn
    {
        return $this->column;
    }
}