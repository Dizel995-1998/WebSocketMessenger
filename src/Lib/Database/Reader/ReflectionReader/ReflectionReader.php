<?php

namespace Lib\Database\Reader\ReflectionReader;

use InvalidArgumentException;
use Lib\Container\Container;
use Lib\Database\Column\BaseColumn;
use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\StringColumn;
use Lib\Database\Reader\IReader;
use Lib\Database\Relations\BaseRelation;
use Lib\Database\Relations\OneToMany;
use Lib\Database\Relations\OneToOne;
use Rakit\Validation\Validator;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

class ReflectionReader implements IReader
{
    const PROPERTY_FILTER = ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

    const PROPERTIES_TYPES = [IntegerColumn::class, StringColumn::class];
    const RELATIONS_TYPES = [OneToOne::class, OneToMany::class];

    protected string $entityClassName;
    protected string $tableName;
    protected ?string $primaryKeyColumn = null;
    protected ?string $primaryKeyProperty = null;
    protected array $properties = [];
    protected array $relations = [];

    /**
     * @todo Нужно было вынести метод установки классов в интерфейс и реализовать его
     * @param array $ormClasses
     */
    public function __construct(protected array $ormClasses)
    {
        foreach ($this->ormClasses as $class) {
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" does not exist', $class));
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

    protected function validate(array $inputData, array $validateRules) : void
    {
        $validation = $this->validator->make($inputData, $validateRules);
        $validation->validate();

        // fixme: костыль с распечатыванием ошибки
        if ($validation->fails()) {
            throw new RuntimeException(implode(', ', $validation->errors->all()));
        }
    }

    /**
     * @param string $phpDoc
     * @param string $propertyName
     * @return BaseColumn|null
     * @throws ReflectionException
     */
    protected function getColumn(string $phpDoc, string $propertyName) : ?BaseColumn
    {
        $regexPattern = '/' . '(?<column>' . implode('|', $this->getShortClassNames(self::PROPERTIES_TYPES)) . ') ?\((?<json>.*)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (($jsonDecode = json_decode($matches['json'], true)) === false) {
            throw new InvalidArgumentException('Cannot parse json, reason: ' . json_last_error_msg());
        }

        $nameSpace = 'Lib\\Database\\Column\\';

        // todo: если нет ключа name, можно использовать camelCase от названия свойства explodeCamelCase method

        $jsonDecode['name'] = $jsonDecode['name'] ?: $this->explodeCamelCase($propertyName);
        $columnClassName = $nameSpace . $matches['column'];
        $isPK = $jsonDecode['isPrimaryKey'] ?: false;

        return (new ($columnClassName)(
            $jsonDecode['name'],
            $this->tableName,
            $isPK,
            $jsonDecode['nullable'] ?: false,
            $jsonDecode['length'] ?: null,
            $jsonDecode['default_value'] ?: null
        ));
    }

    /**
     * @throws ReflectionException
     */
    protected function getRelation(string $phpDoc) : ?BaseRelation
    {
        $regexPattern = '/' . '(?<relation>' . implode('|', $this->getShortClassNames(self::RELATIONS_TYPES)) . ') ?\((?<json>.+)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        // fixme: хардкод неймспейсов
        $nameSpace = '\\Lib\\Database\\Relations\\';
        $nameSpaceEntity = 'Entity\\';
        $targetTable = $this->getTableNameByEntity($nameSpaceEntity . $jsonDecode['targetEntity']);

        if (!$jsonDecode['name']) {
            $jsonDecode['name'] = $this->getPrimaryColumn();
        }

        return (new ($nameSpace . $matches['relation'])(
            $jsonDecode['name'],
            $this->tableName,
            $jsonDecode['mappedBy'],
            $targetTable)
        );
    }

    /**
     * @example helloWorld => hello_world
     * @param string $expression
     * @return string
     */
    protected function explodeCamelCase(string $expression) : string
    {
        $arParts = preg_split('/(?:[a-z]+|[A-Z][a-z]+)\K(?=[A-Z])/', $expression, -1, PREG_SPLIT_NO_EMPTY);

        return implode('_', array_map(function ($item) {
            return strtolower($item);
        }, $arParts));
    }

    /**
     * @throws ReflectionException
     */
    protected function parseColumns(ReflectionProperty ...$reflectionProperties) : void
    {
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($column = $this->getColumn($reflectionProperty->getDocComment(), $reflectionProperty->getName())) {
                $this->properties[$reflectionProperty->getName()] = $column;

                if ($column->isPrimaryKey()) {
                    $this->primaryKeyProperty = $reflectionProperty->getName();
                    $this->primaryKeyColumn = $column->getName();
                }
            }
        }
    }

    // todo обновить интерфейс ридера
    protected function getColumnByName(string $columnName) : ?BaseColumn
    {
        foreach ($this->properties as $propertyName => $column) {
            if ($column->getName() == $columnName) {
                return $column;
            }
        }

        return null;
    }

    protected function parseRelations(ReflectionProperty ...$reflectionProperties) : void
    {
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($relation = $this->getRelation($reflectionProperty->getDocComment())) {
                // fixme: жёсткий костыль, если у свойства отношения указано название колонки, считаем что это физ.колонка в БД, и её нужно прокинуть в свойство сущности
                if ($relation->getSourceColumn() != $this->primaryKeyColumn) {
                    $targetEntityClassName = $this->getEntityClassNameByTable($relation->getTargetTable());

                    $targetColumn = (Container::getService(IReader::class))
                        ->readEntity($targetEntityClassName)
                        ->getColumnByName($relation->getTargetColumn());

                    // fixme: для параметра nullable реализовать в связах параметр обязательности связи.
                    $column = $targetColumn instanceof StringColumn ?
                        new StringColumn($relation->getSourceColumn(), $this->tableName) :
                        new IntegerColumn($relation->getSourceColumn(), $this->tableName);

                    $this->properties[$reflectionProperty->getName()] = $column;
                }

                $this->relations[$reflectionProperty->getName()] = $relation;
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    protected function parse() : void
    {
        $reflectionClass = new ReflectionClass($this->entityClassName);
        // todo: на данный момент нет чтения пхп дока класса и название таблицы для сущности формируется из её имени
        $this->tableName = $this->explodeCamelCase($reflectionClass->getShortName()) . 's';

        $reflectionProperties = $reflectionClass->getProperties(self::PROPERTY_FILTER);
        $this->parseColumns(...$reflectionProperties);
        $this->parseRelations(...$reflectionProperties);

        if (empty($this->primaryKeyProperty) || empty($this->primaryKeyColumn)) {
            throw new RuntimeException(sprintf('У сущности "%s" отсутствует первичный ключ', $this->entityClassName));
        }
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
        foreach ($this->properties as $propertyName => $column) {
            if ($column->getName() == $columnName) {
                return $propertyName;
            }
        }

        return null;
    }

    protected function getHashMapOrmToTable() : array
    {
        $res = [];

        /** fixme: костыль, завязываемся на использование названий классов в качестве названий таблиц */
        foreach ($this->ormClasses as $class) {
            $res[$class] = $this->explodeCamelCase(current($this->getShortClassNames([$class]))) . 's';
        }

        return $res;
    }

    public function getTableNameByEntity(string $entityClassName): ?string
    {
        return $this->getHashMapOrmToTable()[$entityClassName];
    }

    public function getEntityClassNameByTable(string $tableName): ?string
    {
        return array_flip($this->getHashMapOrmToTable())[$tableName];
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
        $this->relations = [];
        $this->primaryKeyProperty = null;
        $this->primaryKeyProperty = null;
    }

    /**
     * @throws ReflectionException
     */
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