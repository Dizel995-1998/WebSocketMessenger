<?php

namespace Lib\Database\Reader\ReflectionReader;

use Entity\Picture;
use Entity\User;
use InvalidArgumentException;
use Lib\Database\Column\BaseColumn;
use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\PrimaryKey;
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

    const PROPERTIES_TYPES = [IntegerColumn::class, StringColumn::class, PrimaryKey::class];
    const RELATIONS_TYPES = [OneToOne::class, OneToMany::class];

    protected string $entityClassName;
    protected string $tableName;
    protected ?string $primaryKeyColumn = null;
    protected ?string $primaryKeyProperty = null;
    protected array $properties = [];
    protected array $relations = [];

    public function __construct(protected Validator $validator) { }

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
     * @return BaseColumn|null
     * @throws ReflectionException
     */
    protected function getColumn(string $phpDoc) : ?BaseColumn
    {
        $regexPattern = '/' . '(?<column>' . implode('|', $this->getShortClassNames(self::PROPERTIES_TYPES)) . ') ?\((?<json>.+)\)/';

        if (!preg_match($regexPattern, $phpDoc, $matches)) {
            return null;
        }

        if (!$jsonDecode = json_decode($matches['json'], true)) {
            throw new InvalidArgumentException('Cannot parse json, reason:' . json_last_error_msg());
        }

        $this->validate($jsonDecode, [
            'name' => 'required',
            'nullable' => 'boolean|default:true',
        ]);

        $nameSpace = 'Lib\\Database\\Column\\';

        // todo: если нет ключа name, можно использовать camelCase от названия свойства explodeCamelCase method

        $columnClassName = $nameSpace . $matches['column'];
        $isPK = $columnClassName == PrimaryKey::class;

        return (new ($columnClassName)($jsonDecode['name'], $jsonDecode['nullable'] ?: false, $isPK, $jsonDecode['default_value'] ?: null));
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

        $this->validate($jsonDecode, [
            'name' => 'alpha_dash',
            'mappedBy' => 'required|alpha_dash'
        ]);

        // fixme: хардкод неймспейсов
        $nameSpace = '\\Lib\\Database\\Relations\\';
        $nameSpaceEntity = 'Entity\\';
        $targetTable = self::getTableNameByEntity($nameSpaceEntity . $jsonDecode['targetEntity']);

        if (!$jsonDecode['name']) {
            $jsonDecode['name'] = $this->getPrimaryColumn();
        }

        return (new ($nameSpace . $matches['relation'])($jsonDecode['name'], $this->tableName, $jsonDecode['mappedBy'], $targetTable));
    }

    /**
     * @example HelloWorld => hello_world
     * @param string $className
     * @return string
     */
    protected function explodeCamelCase(string $className) : string
    {
        $arParts = preg_split('/(?:[A-Z]+|[A-Z][a-z]+)\K(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY);

        return implode('_', array_map(function ($item) {
            return strtolower($item);
        }, $arParts));
    }

    /**
     * @throws ReflectionException
     */
    protected function parse() : void
    {
        $reflectionClass = new ReflectionClass($this->entityClassName);

        // todo: на данный момент нет чтения пхп дока класса и название таблицы для сущности формируется из её имени
        $this->tableName = $this->explodeCamelCase($reflectionClass->getShortName()) . 's';

        foreach ($reflectionClass->getProperties(self::PROPERTY_FILTER) as $reflectionProperty) {

            if ($column = $this->getColumn($reflectionProperty->getDocComment())) {
                $this->properties[$reflectionProperty->getName()] = $column;

                if ($column instanceof PrimaryKey) {
                    $this->primaryKeyProperty = $reflectionProperty->getName();
                    $this->primaryKeyColumn = $column->getName();
                }

                continue;
            }

            if ($relation = $this->getRelation($reflectionProperty->getDocComment())) {
                $this->relations[$reflectionProperty->getName()] = $relation;
            }
        }

        if (!$this->primaryKeyProperty) {
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

    public static function getTableNameByEntity(string $entityClassName): ?string
    {
        // fixme: нужен построитель карты ORM сущностей
        $mapOfOrmEntities = [
            Picture::class => 'pictures',
            User::class => 'users'
        ];

        return $mapOfOrmEntities[$entityClassName];
    }

    public static function getEntityClassNameByTable(string $tableName): ?string
    {
        // fixme: нужен построитель карты ORM сущностей + flip
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