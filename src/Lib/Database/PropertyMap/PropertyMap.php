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
    protected string $propertyName;

    protected string $columnName;

    public function __construct(string $propertyName, string $columnName)
    {
        $this->propertyName = $propertyName;
        $this->columnName = $columnName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }
}