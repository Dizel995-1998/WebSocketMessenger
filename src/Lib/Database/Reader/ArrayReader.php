<?php

namespace Lib\Database\Reader;

class ArrayReader implements IReader
{
    protected array $propertiesMapping;

    protected string $tableName;

    protected string $entityName;

    protected array $associations = [];

    protected ?string $primaryKey = null;

    public function __construct(array $arData)
    {
        $this->propertiesMapping = $arData['mapping'];
        $this->tableName = $arData['table_name'];
        $this->entityName = $arData['entity_name'];
        $this->associations = $arData['associations'] ?? [];
        $this->primaryKey = $arData['primary_key'] ?? null;
    }

    public function getProperties(): array
    {
        return $this->propertiesMapping;
    }

    public function getPropertiesList(): array
    {
        return array_keys($this->propertiesMapping);
    }

    public function getColumns(): array
    {
        return array_values($this->propertiesMapping);
    }

    public function getColumnNameByProperty(string $propertyName): ?string
    {
//        return $this->properties[$propertyName];
    }

    public function getPropertyNameByColumn(string $columnName): ?string
    {
//        return $this->columns[$columnName];
    }

    public static function getTableNameByEntity(string $entityClassName): ?string
    {
        // TODO: Implement getTableNameByEntity() method.
    }

    public static function getEntityClassNameByTable(string $tableName): ?string
    {
        // TODO: Implement getEntityClassNameByTable() method.
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getRelations(): array
    {
        return $this->associations;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }
}