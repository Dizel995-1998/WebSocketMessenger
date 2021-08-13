<?php

namespace Lib\Command\Interfaces;

interface InputInterface
{
    public function getCountOfArgs() : int;

    public function getArgument(string $key, bool $required = false) : ?string;

    public function getScriptName() : string;
}