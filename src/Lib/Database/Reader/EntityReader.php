<?php

namespace Lib\Database\Reader;

class EntityReader
{
    protected array $entityMapping = [];

    protected array $relations = [];

    /**
     * TODO тестовый метод, дропнуть
     */
    public function setEntityMapping(array $mapping): self
    {
        $this->entityMapping = $mapping;
        return $this;
    }

    /**
     * TODO тестовый метод, удалить
     */
    public function setEntityRelations(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }

    public function getEntityMapping(): array
    {
        return $this->entityMapping;
    }

    public function getEntityRelations(): array
    {
        return $this->relations;
    }
}