<?php

namespace Lib\Database\Reader;

use Lib\Database\Column\IntegerColumn;
use Lib\Database\Column\StringColumn;
use ReflectionProperty;


class ReflectionReader implements IReader
{
    const DOC_COMMENT_PREFIX = 'ORM';

    const PROPERTY_FILTER = ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

    const ALLOW_COLUMN_TYPES = [
        IntegerColumn::class,
        StringColumn::class
    ];

    protected string $entityClassName;

    /**
     * @var <string, ReflectionProperty>[]
     */
    protected array $properties = [];

    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @throws \ReflectionException
     */
    public function getProperties(): array
    {
        if ($this->properties) {
            return array_keys($this->properties);
        }

        $reflectionClass = new \ReflectionClass($this->entityClassName);

        foreach ($reflectionClass->getProperties(self::PROPERTY_FILTER) as $reflectionProperty) {
            $this->properties[$reflectionProperty->getName()] = $reflectionProperty;
        }

        return array_keys($this->properties);
    }

    public function getColumns(): array
    {
        $res = [];
        $this->getProperties();

        foreach ($this->properties as $propertyName => $reflectionProperty) {
            if ($column = $this->getColumnNameByProperty($propertyName)) {
                $res[] = $column;
            }
        }

        return $res;
    }

    /**
     * TODO стоит вынести в глобальные функции
     * @param string $classNameWithNamespace
     * @return string|null
     */
    protected function getNamespaceByClassName(string $classNameWithNamespace) : ?string
    {
        if (preg_match(sprintf('~(?<namespace>\S+\\)~'), $classNameWithNamespace, $matches)) {
            return $matches['namespace'];
        }

        return null;
    }

    protected function getClassNameWithoutNamespace(string $classNameWithNamespace) : string
    {
        $path = explode('\\', $classNameWithNamespace);
        return array_pop($path);
    }

    public function getColumnNameByProperty(string $propertyName): ?string
    {
        $this->getProperties();

        /*** @var ReflectionProperty */
        if (!isset($this->properties[$propertyName])) {
            return null;
        }

        $phpDoc = $this->properties[$propertyName]->getDocComment();
        $arDocComment = explode(PHP_EOL, $phpDoc);

        foreach ($arDocComment as $rowDocComment) {
            $allowedColumnsTypesWithoutNamespaces = array_map(function ($item) {
                return $this->getClassNameWithoutNamespace($item);
            }, self::ALLOW_COLUMN_TYPES);

            $regexPattern = sprintf('~@%s\\\(?<column_type>%s)\((?<json_parameters>(.+))\)~', self::DOC_COMMENT_PREFIX, implode('|', $allowedColumnsTypesWithoutNamespaces));

            if (!preg_match($regexPattern, $rowDocComment, $matches)) {
                continue;
            }

            if (!$arJson = json_decode($matches['json_parameters'], true)) {
                $error = sprintf('Error during parsing php doc of %s entity, property %s, json error: %s',
                    $this->entityClassName,
                    $propertyName,
                    json_last_error_msg()
                );

                throw new \RuntimeException($error);
            }

            // todo нужна нормальная валидация
            if (!isset($arJson['name'])) {
                throw new \RuntimeException(sprintf('Missing required key name, in property: %s, entity: %s',
                    $propertyName,
                    $this->entityClassName
                ));
            }

            return $arJson['name'];
        }

        return null;
    }

    public function getPropertyNameByColumn(string $columnName): ?string
    {
        // TODO: Implement getPropertyNameByColumn() method.
    }

    public static function getTableNameByEntity(string $entityClassName): ?string
    {
        $phpDoc = (new \ReflectionClass($entityClassName))->getDocComment();



    }

    public static function getEntityClassNameByTable(string $tableName): ?string
    {
        // TODO: Implement getEntityClassNameByTable() method.
    }
}