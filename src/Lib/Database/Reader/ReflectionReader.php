<?php

namespace Lib\Database\Reader;

use ReflectionProperty;

class ReflectionReader implements IReader
{
    const DOC_COMMENT_PREFIX = 'ORM';

    const PROPERTY_FILTER = ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

    const DEFAULT_PRIMARY_KEY_COLUMN = 'id';

    protected string $entityClassName;

    protected string $tableName;

    protected string $primaryKeyColumn;

    protected array $properties = [];

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
        $this->parse();
    }

    protected function parse() : void
    {
        $reflectionClass = new \ReflectionClass($this->entityClassName);

        if (!$this->tableName = $this->extractTableNameFromDoc((string) $reflectionClass->getDocComment())) {
            throw new \InvalidArgumentException(sprintf('Entity "%s" does not table name in php doc', $this->entityClassName));
        }

        foreach ($reflectionClass->getProperties(self::PROPERTY_FILTER) as $reflectionProperty) {
            if (!$this->isValidOrmDoc((string) $reflectionProperty->getDocComment())) {
                continue;
            }

            $this->properties[$reflectionProperty->getName()] = $this->getColumnName($reflectionProperty->getDocComment());
        }

        $this->primaryKeyColumn  = $this->primaryKeyColumn ?? self::DEFAULT_PRIMARY_KEY_COLUMN;
    }

    protected function extractTableNameFromDoc(string $phpDoc) : ?string
    {
        return $this->getColumnName($phpDoc) ?: null;
    }

    protected function getColumnName(string $phpDoc, ?string $defaultColumn = null) : string
    {
        $regexPattern = '~ORM\\S+(?<json>({(.*)}))~';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            throw new \InvalidArgumentException('Column was dont found in phpDoc');
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new \InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        if (!isset($jsonDecode['NAME']) && !isset($jsonDecode['name']) && !$defaultColumn) {
            throw new \InvalidArgumentException('Cannot find name column');
        }

        return $jsonDecode['name'] ?: $jsonDecode['NAME'] ?: $defaultColumn;
    }

    protected function isPrimaryKey() : bool
    {

    }

    protected function isRelation() : bool
    {

    }

    protected function isValidOrmDoc(string $phpDoc) : bool
    {
        return str_contains($phpDoc, self::DOC_COMMENT_PREFIX);
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKeyColumn;
    }

    public function getEntityName(): string
    {
        return $this->entityClassName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getColumns(): array
    {
        // TODO: Implement getColumns() method.
    }

    public function getColumnNameByProperty(string $propertyName): ?string
    {
        // TODO: Implement getColumnNameByProperty() method.
    }

    public function getPropertyNameByColumn(string $columnName): ?string
    {
        // TODO: Implement getPropertyNameByColumn() method.
    }

    public static function getTableNameByEntity(string $entityClassName): ?string
    {
        // TODO: Implement getTableNameByEntity() method.
    }

    public static function getEntityClassNameByTable(string $tableName): ?string
    {
        // TODO: Implement getEntityClassNameByTable() method.
    }

    public function getRelations(): array
    {
        return [];
    }
}