<?php

namespace Service\VirtualClass;

class VirtualProperty
{
    const NOT_TYPE = 'none';
    const ALLOW_TYPES = ['int', 'string', 'array', 'float', self::NOT_TYPE];
    const ALLOW_MODIFIERS = ['protected', 'public', 'private'];

    protected string $name;
    protected string $type;
    protected string $modifier;
    protected bool $nullable;
    protected $defaultValue;

    public function __construct(string $name, string $modifier = 'protected', ?string $type = null, bool $nullable = true, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type ?: self::NOT_TYPE;
        $this->nullable = $nullable;
        $this->modifier = $modifier;
        $this->defaultValue = $defaultValue;
        $this->checkAllowValue($this->modifier, self::ALLOW_MODIFIERS);
        $this->checkAllowValue($this->type, self::ALLOW_TYPES);
    }

    /**
     * @param string $searchValue
     * @param array $haystack
     */
    protected function checkAllowValue(string $searchValue, array $haystack) : void
    {
        if (!in_array($searchValue, $haystack)) {
            throw new \InvalidArgumentException(sprintf('Type must be one of "%s", "%s" given', implode(',', $haystack), $searchValue));
        }
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getModifier() : string
    {
        return $this->modifier;
    }

    public function getType() : ?string
    {
        return $this->type != self::NOT_TYPE ?
            $this->type :
            null;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return bool
     */
    public function isNullable() : bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue() : bool
    {
        return $this->defaultValue !== null;
    }
}