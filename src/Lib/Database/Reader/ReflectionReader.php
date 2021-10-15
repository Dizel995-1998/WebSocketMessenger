<?php

namespace Lib\Database\Reader;

use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\PrimaryKey;
use Lib\Database\Column\StringColumn;
use ReflectionProperty;

class ReflectionReader implements IReader
{
    const DOC_COMMENT_PREFIX = 'ORM';
    const DOC_TABLE_NAME = 'Table';

    const PROPERTY_FILTER = ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;
    const DEFAULT_PRIMARY_KEY_COLUMN = 'id';

    const ALLOW_COLUMN_TYPES = [IntegerColumn::class, StringColumn::class, PrimaryKey::class];

    protected string $entityClassName;
    protected string $tableName;
    protected ?string $primaryKeyColumn = null;
    protected ?string $primaryKeyProperty = null;
    protected array $properties = [];

    /**
     * Возвращает название класса без namespace
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    protected function getShortClassName(string $className) : string
    {
        return (new \ReflectionClass($className))->getShortName();
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

            if ($columnName = $this->getDataByPhpTag($reflectionProperty->getDocComment(), [$this->getShortClassName(PrimaryKey::class)])) {
                $this->primaryKeyProperty = $reflectionProperty->getName();
                $this->primaryKeyColumn = $columnName;
            }

            /** Процедура избавления названий классов от namespace, чтобы в будущем замапиться на ORM объекты типов */
            // fixme: сделано черезчур громоздко
            $allowOrmTypes = [];

            foreach (self::ALLOW_COLUMN_TYPES as $allowType) {
                $allowOrmTypes[] = $this->getShortClassName($allowType);
            }

            /*** Название свойства -> Название колонки */
            $this->properties[$reflectionProperty->getName()] = $this->getDataByPhpTag($reflectionProperty->getDocComment(), $allowOrmTypes);
        }

        // fixme: убрать, если у сущности нет первичного ключа, это не значит что ей нужен дефолтный
        $this->primaryKeyProperty = $this->primaryKeyProperty ?? 'id';
        $this->primaryKeyColumn  = $this->primaryKeyColumn ?? self::DEFAULT_PRIMARY_KEY_COLUMN;
    }

    protected function extractTableNameFromDoc(string $phpDoc) : ?string
    {
        return $this->getDataByPhpTag($phpDoc, [self::DOC_TABLE_NAME]);
    }

    protected function getDataByPhpTag(string $phpDoc, array $allowTypes) : ?string
    {
        $regexPattern = '/' . self::DOC_COMMENT_PREFIX . '\\\(?<column>' . implode('|', $allowTypes) . ') ?\((?<json>.+)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new \InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        if (!isset($jsonDecode['NAME']) && !isset($jsonDecode['name'])) {
            throw new \InvalidArgumentException('Cannot find name column');
        }

        return $jsonDecode['name'] ?: $jsonDecode['NAME'];
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
        return array_values($this->properties);
    }

    public function getColumnNameByProperty(string $propertyName): ?string
    {
        return $this->properties[$propertyName];
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

    /**
     * Чистит данные от предыдущей сущности
     */
    protected function cleanData() : void
    {
        $this->properties = [];
        $this->primaryKeyProperty = null;
        $this->primaryKeyProperty = null;
    }

    public function readEntity(string $entityClassName): self
    {
        $this->entityClassName = $entityClassName;
        $this->cleanData();
        $this->parse();
        return $this;
    }

    public function getPrimaryColumn(): ?string
    {
        return $this->primaryKeyColumn;
    }

    public function getPrimaryProperty(): ?string
    {
        return $this->primaryKeyProperty;
    }
}