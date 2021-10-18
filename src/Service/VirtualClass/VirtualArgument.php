<?php

namespace Service\VirtualClass;

class VirtualArgument
{
    const NOTHING_TYPE = 'none';
    const ALLOW_TYPES = ['int', 'string', 'array', 'float', self::NOTHING_TYPE];

    public function __construct(
        protected string $name,
        protected string $type = self::NOTHING_TYPE
    ) {
        $this->checkAllowValue($this->type, self::ALLOW_TYPES);
    }

    /**
     * todo дублирование кода в аргументах и VirtualProperty
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
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type != self::NOTHING_TYPE ? $this->type : null;
    }
}