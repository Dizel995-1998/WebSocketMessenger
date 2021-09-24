<?php

namespace Lib\Database\Property;

class Property
{
    protected string $name;
    protected ?string $type = null;

    public function __construct(string $name, ?string $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
