<?php

namespace Lib\Database\Reader\ReflectionReader;

use InvalidArgumentException;
use Lib\Database\Column\BaseColumn;
use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\StringColumn;
use Lib\Database\Reader\IReader;
use Lib\Database\Relations\BaseRelation;
use Lib\Database\Relations\OneToMany;
use Lib\Database\Relations\OneToOne;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class Reader implements IReader
{
    const PROPERTY_FILTER = ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;
    const NAMESPACE_COLUMNS = 'Lib\\Database\\Column\\';
    const NAMESPACE_RELATIONS = 'Lib\\Database\\Relations\\';
    const NAMESPACE_ENTITY = 'Entity\\';

    const PROPERTIES_TYPES = [IntegerColumn::class, StringColumn::class];
    const RELATIONS_TYPES = [OneToOne::class, OneToMany::class];
    const TABLE_NAME = 'table_name';
    const PROPERTIES = 'properties';
    const COLUMNS = 'columns';
    const PRIMARY_KEY_PROPERTY = 'primary_key_property';
    const PRIMARY_KEY_COLUMN = 'primary_key_column';
    const RELATIONS = 'relations';

    protected array $entities = [];

    public function loadOrmClasses(string ...$classes) : self
    {
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" does not exist', $class));
            }

            $properties = $this->setAccessableProperties(...(new \ReflectionClass($class))->getProperties(self::PROPERTY_FILTER));

            $this->entities[$class] = [
                self::TABLE_NAME => $this->formTableNameByClassName($class),
                self::PROPERTIES => $properties
            ];
        }

        $this->buildSchema();
        return $this;
    }

    /**
     * @param ReflectionProperty ...$reflectionProperties
     * @return <string, ReflectionProperty>
     */
    protected function setAccessableProperties(ReflectionProperty ...$reflectionProperties) : array
    {
        $res = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $res[$reflectionProperty->getName()] = $reflectionProperty;
        }

        return $res;
    }

    protected function buildSchema() : void
    {
        foreach ($this->entities as $entityName => $data) {
            $this->parseColumns($entityName, $data[self::PROPERTIES]);
        }

        foreach ($this->entities as $entityName => $data) {
            $this->parseRelations($entityName, $data[self::PROPERTIES]);
        }
    }

    /**
     * @param ReflectionProperty[] $reflectionProperties
     * @throws ReflectionException
     */
    protected function parseColumns(string $entityName, array $reflectionProperties) : void
    {
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($column = $this->getColumn(
                    $reflectionProperty->getDocComment(),
                    $reflectionProperty->getName(),
                    $this->getTableNameByEntity($entityName))
            ) {
                $this->entities[$entityName][self::COLUMNS][$reflectionProperty->getName()] = $column;

                if ($column->isPrimaryKey()) {
                    $this->entities[$entityName][self::PRIMARY_KEY_PROPERTY] = $reflectionProperty->getName();
                    $this->entities[$entityName][self::PRIMARY_KEY_COLUMN] = $column->getName();
                }
            }
        }
    }

    /**
     * Возвращает название класса без namespace
     * @param array $classNames
     * @return array
     * @throws ReflectionException
     */
    protected function getShortClassNames(array $classNames = []) : array
    {
        $res = [];

        foreach ($classNames as $className) {
            $res[] = (new ReflectionClass($className))->getShortName();
        }

        return $res;
    }

    /**
     * @param string $phpDoc
     * @param string $propertyName
     * @return BaseColumn|null
     * @throws ReflectionException
     */
    protected function getColumn(string $phpDoc, string $propertyName, string $tableName) : ?BaseColumn
    {
        $regexPattern = '/' . '(?<column>' . implode('|', $this->getShortClassNames(self::PROPERTIES_TYPES)) . ') ?\((?<json>.*)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (($jsonDecode = json_decode($matches['json'], true)) === false) {
            throw new InvalidArgumentException('Cannot parse json, reason: ' . json_last_error_msg());
        }

        $jsonDecode['name'] = $jsonDecode['name'] ?: $propertyName;
        $columnClassName = self::NAMESPACE_COLUMNS . $matches['column'];
        $isPK = $jsonDecode['isPrimaryKey'] ?: false;

        return (new ($columnClassName)(
            $jsonDecode['name'],
            $tableName,
            $isPK,
            $jsonDecode['nullable'] ?: false,
            $jsonDecode['length'] ?: null,
            $jsonDecode['default_value'] ?: null
        ));
    }

    /**
     * @param ReflectionProperty[] $reflectionProperties
     */
    protected function parseRelations(string $className, array $reflectionProperties) : void
    {
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($relation = $this->getRelation($reflectionProperty->getDocComment(), $className)) {
                $this->entities[$className][self::RELATIONS][$reflectionProperty->getName()] = $relation;

                // временный хардкод, т.к в случае некоторых связей необходимо наличие колонки, а она не задекларирована, т.к в аннотациях прописана отношение а не колонка
                if ($relation->getSourceColumn() != $this->getPkColumnByEntity($className)) {
                    $this->entities[$className][self::COLUMNS][$reflectionProperty->getName()] = new IntegerColumn($relation->getSourceColumn(), $this->getTableNameByEntity($className));
                }
            }
        }
    }

    /**
     * @todo Привести в более надлежащее качество
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    protected function formTableNameByClassName(string $className) : string
    {
        $shortName = (new \ReflectionClass($className))->getShortName();
        $arParts = explode('\\', $shortName);
        $shortClassName = end($arParts);
        $arParts = preg_split('/(?:[a-z]+|[A-Z][a-z]+)\K(?=[A-Z])/', $shortClassName, -1, PREG_SPLIT_NO_EMPTY);
        return (strtolower(end($arParts)) . 's');
    }

    public function getTableNameByEntity(string $entityClassName): ?string
    {
        return $this->entities[$entityClassName][self::TABLE_NAME];
    }

    public function getEntityClassNameByTable(string $tableName): ?string
    {
        foreach ($this->entities as $entity => $data) {
            if ($data[self::TABLE_NAME] == $tableName) {
                return $entity;
            }
        }

        return null;
    }

    public function getPkColumnByEntity(string $entity): ?string
    {
        return $this->entities[$entity][self::PRIMARY_KEY_COLUMN];
    }

    public function getPkPropertyByEntity(string $entity): ?string
    {
        return $this->entities[$entity][self::PRIMARY_KEY_PROPERTY];
    }

    public function getEntityNameByTable(string $tableName): ?string
    {
        foreach ($this->entities as $className => $data) {
            if ($data[self::TABLE_NAME] == $tableName) {
                return $className;
            }
        }

        return null;
    }

    public function getProperties(string $entityName): ?array
    {
        return $this->entities[$entityName][self::PROPERTIES];
    }

    public function getColumns(string $entityName): ?array
    {
        return $this->entities[$entityName][self::COLUMNS];
    }

    public function getColumnNameByProperty(string $entity, string $propertyName): ?BaseColumn
    {
        foreach ($this->entities[$entity][self::COLUMNS] as $propName => $column) {
            if ($propertyName == $propName) {
                return $column;
            }
        }

        return null;
    }

    public function getPropertyNameByColumn(string $entity, string $columnName): ?string
    {
        foreach ($this->entities[$entity][self::COLUMNS] as $propertyName => $column) {
            if ($columnName == $column->getName()) {
                return $propertyName;
            }
        }

        foreach ($this->entities[$entity][self::RELATIONS] as $propertyName => $relation) {
            if ($columnName == $relation->getSourceColumn()) {
                return $propertyName;
            }
        }

        return null;
    }

    public function getRelations(string $className): array
    {
        return $this->entities[$className][self::RELATIONS] ?? [];
    }

    /**
     * @throws ReflectionException
     */
    protected function getRelation(string $phpDoc, string $entityOwner) : ?BaseRelation
    {
        $regexPattern = '/' . '(?<relation>' . implode('|', $this->getShortClassNames(self::RELATIONS_TYPES)) . ') ?\((?<json>.+)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        if (!$jsonDecode['name'] && !($jsonDecode['name'] = $this->getPkColumnByEntity($entityOwner))) {
            throw new \RuntimeException(sprintf('Отсутствует привязка к колонке у сущности %s', $entityOwner));
        }

        if (!$sourceTable = $this->getTableNameByEntity($entityOwner)) {
            throw new \RuntimeException(sprintf('For entity "%s" was not found table', $entityOwner));
        }

        if (!$targetTable = $this->getTableNameByEntity(self::NAMESPACE_ENTITY . $jsonDecode['targetEntity'])) {
            throw new \RuntimeException(sprintf('For entity "%s" was not found table', self::NAMESPACE_ENTITY . $jsonDecode['targetEntity']));
        }

        if (!$mappedBy = $jsonDecode['mappedBy']) {
            throw new \RuntimeException(sprintf('Mapped by was not found in "%s" entity', $entityOwner));
        }

        $relationData = [
            'source_table'  =>  $sourceTable,
            'source_column' =>  $jsonDecode['name'],
            'target_table'  =>  $targetTable,
            'target_column' =>  $mappedBy
        ];

        return (new (self::NAMESPACE_RELATIONS . $matches['relation'])($relationData));
    }

    public function getReflectionProperty(string $entity, string $propertyName): ?\ReflectionProperty
    {
        return $this->entities[$entity][self::PROPERTIES][$propertyName];
    }
}