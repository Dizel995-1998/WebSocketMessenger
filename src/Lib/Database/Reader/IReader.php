<?php

namespace Lib\Database\Reader;

use Lib\Database\Column\BaseColumn;
use Lib\Database\Relations\BaseRelation;

interface IReader
{
    public function loadOrmClasses(string ...$classes) : self;

    public function getPkColumnByEntity(string $entity) : ?string;

    public function getPkPropertyByEntity(string $entity) : ?string;

    public function getTableNameByEntity(string $entity) : ?string;

    public function getEntityNameByTable(string $tableName) : ?string;

    /**
     * @return <string, BaseColumn> (PropertyName => BaseColumn)
     */
    public function getProperties(string $entityName) : ?array;

    /**
     * @return BaseColumn[]
     */
    public function getColumns(string $entityName) : ?array;

    /**
     * @param string $propertyName
     * @return string|null
     */
    public function getColumnNameByProperty(string $entity, string $propertyName) : ?BaseColumn;

    /**
     * @param string $columnName
     * @return string|null
     */
    public function getPropertyNameByColumn(string $entity, string $columnName) : ?string;

    /**
     * @param string $tableName
     * @return string|null
     */
    public function getEntityClassNameByTable(string $tableName) : ?string;

    /**
     * @return <string, BaseRelation>
     */
    public function getRelations(string $className) : array;

    public function getReflectionProperty(string $entity, string $propertyName) : ?\ReflectionProperty;
}