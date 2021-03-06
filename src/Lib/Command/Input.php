<?php

namespace Lib\Command;

use Lib\Command\Interfaces\InputInterface;

class Input implements InputInterface
{
    private array $args;

    public function __construct(array $args)
    {
        array_shift($args);
        $this->args = $args;
    }

    public function getCountOfArgs(): int
    {
        return count($this->args);
    }

    public function getArgument(string $key, bool $required = false): ?string
    {
        $cmdLine = implode(' ', $this->args);
        if (preg_match("~-{$key}\s(?<value>\S+)~", $cmdLine, $matches)) {
            return $matches['value'];
        }

        if ($required) {
            throw new \InvalidArgumentException(sprintf('Missing required arg: %s', $key));
        }

        return null;
    }

    public function getScriptName(): string
    {
        return reset($this->args);
    }
}