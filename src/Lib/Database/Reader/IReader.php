<?php

namespace Lib\Database\Reader;

use Lib\Database\Column\BaseColumn;
use Lib\Database\Relations\BaseRelation;

interface IReader
{
    public function readEntity(string $entityClassName) : self;

    public function getPrimaryColumn() : ?string;

    public function getPrimaryProperty() : ?string;

    public function getEntityName() : string;

    public function getTableName() : string;

    /**
     * @return <string, BaseColumn> (PropertyName => BaseColumn)
     */
    public function getProperties() : array;

    /**
     * @return string[]
     */
    public function getColumns() : array;

    /**
     * @param string $propertyName
     * @return string|null
     */
    public function getColumnNameByProperty(string $propertyName) : ?BaseColumn;

    /**
     * @param string $columnName
     * @return string|null
     */
    public function getPropertyNameByColumn(string $columnName) : ?string;

    /**
     * @param string $entityClassName
     * @return string
     */
    public static function getTableNameByEntity(string $entityClassName) : ?string;

    /**
     * @param string $tableName
     * @return string|null
     */
    public static function getEntityClassNameByTable(string $tableName) : ?string;

    /**
     * @return <string, BaseRelation>
     */
    public function getRelations() : array;
}