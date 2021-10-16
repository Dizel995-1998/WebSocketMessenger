<?php

namespace Lib\Database\Reader\ReflectionReader;

use Entity\Picture;
use Lib\Database\Column\BaseColumn;
use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\PrimaryKey;
use Lib\Database\Column\StringColumn;
use Lib\Database\Reader\IReader;
use Lib\Database\Relations\BaseRelation;
use Lib\Database\Relations\OneToMany;
use Lib\Database\Relations\OneToOne;
use ReflectionProperty;

class ReflectionReader implements IReader
{
    const PROPERTY_FILTER = ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

    const PROPERTIES_TYPES = [IntegerColumn::class, StringColumn::class, PrimaryKey::class];
    const RELATIONS_TYPES = [OneToOne::class, OneToMany::class];

    protected string $entityClassName;
    protected string $tableName;
    protected ?string $primaryKeyColumn = null;
    protected ?string $primaryKeyProperty = null;
    protected array $properties = [];
    protected array $relations = [];

    /**
     * Возвращает название класса без namespace
     * @param array $classNames
     * @return array
     * @throws \ReflectionException
     */
    protected function getShortClassNames(array $classNames = []) : array
    {
        $res = [];

        foreach ($classNames as $className) {
            $res[] = (new \ReflectionClass($className))->getShortName();
        }

        return $res;
    }


    /**
     * @param string $phpDoc
     * @return BaseColumn|null
     * @throws \ReflectionException
     */
    protected function getColumn(string $phpDoc) : ?BaseColumn
    {
        $regexPattern = '/' . '(?<column>' . implode('|', $this->getShortClassNames(self::PROPERTIES_TYPES)) . ') ?\((?<json>.+)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new \InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        // провалидировать JSON на минимально необходимые поля

        $nameSpace = '\\Lib\\Database\\Column\\';

        return (new ($nameSpace . $matches['column'])($jsonDecode['name'], true));
    }

    protected function getRelation(string $phpDoc) : ?BaseRelation
    {
        $regexPattern = '/' . '(?<relation>' . implode('|', $this->getShortClassNames(self::RELATIONS_TYPES)) . ') ?\((?<json>.+)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new \InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        // провалидировать JSON на минимально необходимые поля

        $nameSpace = '\\Lib\\Database\\Relations\\';

        /**
        protected string $sourceColumn,
        protected string $sourceTable,
        protected string $targetColumn,
        protected string $targetTable,
         */

        return (new ($nameSpace . $matches['relation'])($jsonDecode['name'], 'users', 'id', 'pictures'));
    }

    protected function parse() : void
    {
        $reflectionClass = new \ReflectionClass($this->entityClassName);

        $this->tableName = strtolower($reflectionClass->getShortName()) . 's';

        if ($this->tableName == 'accesstokens') {
            $this->tableName = 'access_tokens';
        }


        foreach ($reflectionClass->getProperties(self::PROPERTY_FILTER) as $reflectionProperty) {

            if ($column = $this->getColumn($reflectionProperty->getDocComment())) {
                $this->properties[$reflectionProperty->getName()] = $column;
                continue;
            }

            if ($relation = $this->getRelation($reflectionProperty->getDocComment())) {
                $this->relations[$reflectionProperty->getName()] = $relation;
            }
        }

        $x = 0;

//        if (!$this->primaryKeyProperty) {
//            throw new \RuntimeException(sprintf('У сущности "%s" отсутствует первичный ключ', $this->entityClassName));
//        }
    }

    protected function extractTableNameFromDoc(string $phpDoc) : ?string
    {
        return $this->getDataByPhpTag($phpDoc, ['Table']);
    }

    protected function getDataByPhpTag(string $phpDoc, array $allowTypes) : ?string
    {
        $regexPattern = '/' . '(?<column>' . implode('|', $allowTypes) . ') ?\((?<json>.+)\)/';

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

    public function getColumnNameByProperty(string $propertyName): ?BaseColumn
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
        return Picture::class;
    }

    /**
     * @return BaseRelation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
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
        return 'id';
        return $this->primaryKeyColumn;
    }

    public function getPrimaryProperty(): ?string
    {
        return $this->primaryKeyProperty;
    }
}