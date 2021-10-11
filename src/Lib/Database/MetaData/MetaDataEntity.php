<?php

namespace Lib\Database\MetaData;

use Lib\Database\Column\BaseColumn;
use Lib\Database\Property\Property;
use Lib\Database\PropertyMap\PropertyMap;
use Lib\Database\Relations\BaseRelation;

class MetaDataEntity
{
    protected string $className;

    protected array $columns = [];

    protected array $properties = [];

    protected array $relations = [];

    protected array $mapping = [];

    protected bool $initialized = false;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return BaseColumn[]
     * @throws \ReflectionException
     */
    public function getColumns() : array
    {
        $this->getMapping();
        return $this->columns;
    }

    /**
     * @return Property[]
     * @throws \ReflectionException
     */
    public function getProperties() : array
    {
        $this->getMapping();
        return $this->properties;
    }

    /**
     * Возвращает название класса сущности
     * @return string
     */
    public function getSourceClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $phpDoc
     * @return array
     */
    protected function explodePhpDoc(string $phpDoc) : array
    {
        return explode(PHP_EOL, $phpDoc);
    }

    /**
     * @param array $phpDoc
     * @return BaseColumn|BaseRelation|null
     */
    protected function findColumn(array $phpDoc)
    {
        /** todo hardcode name of classes  */
        $allowColumnTypes = [
            'IntegerColumn',
            'StringColumn'
        ];

        /** todo hardcode name of classes  */
        $allowRelations = [
            'OneToMany',
            'OneToOne',
        ];

        $res = null;

        foreach ($phpDoc as $doc) {
            if (preg_match('~@(?<column_type>\S+)\((?<json>(.+))\)~', $doc, $matches)) {
                if (!$arJson = json_decode($matches['json'], true)) {
                    throw new \RuntimeException('Error json decode ' . json_last_error_msg());
                }

                /** todo namespace hardcode */
                if (in_array($matches['column_type'], $allowColumnTypes)) {
                    $className = 'Lib\\Database\\Column\\' . $matches['column_type'];
                    return new $className($arJson['name']);
                }

                // todo namespace hardcode
                if (in_array($matches['column_type'], $allowRelations)) {
                    $className = 'Lib\\Database\\Relations\\' . $matches['column_type'];

                    return new $className(
                        $arJson['sourceColumn'],
                        $arJson['sourceTable'],
                        $arJson['targetColumn'],
                        $arJson['targetTable'],
                        $arJson['targetClassName']
                    );
                }
            }
        }

        return $res;
    }

    public function getRelations() : array
    {
        $this->getMapping();
        return $this->relations;
    }

    /**
     * @throws \ReflectionException
     * @return PropertyMap[]
     */
    public function getMapping(): array
    {
        if ($this->initialized) {
            return $this->mapping;
        }

        $reflectorClass = new \ReflectionClass($this->className);
        $properties = $reflectorClass->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $propertyReflection) {
            $arPhpDoc = $this->explodePhpDoc($propertyReflection->getDocComment());

            if (!$column = $this->findColumn($arPhpDoc)) {
                continue;
            }

            if ($column instanceof BaseRelation) {
                $this->relations[$propertyReflection->getName()] = $column;
                continue;
            }

            $property = new Property($propertyReflection->getName());
            $this->columns[] = $column;
            $this->properties[] = $property;
            $this->mapping[] = new PropertyMap($property, $column);
        }

        $this->initialized = true;
        return $this->mapping;
    }
}