<?php

namespace Lib\Database\Drivers;

class DbResult
{
    public function __construct(
        protected array $dbData,
        protected array $errors = []
    ) {

    }

    public function fetch() : array
    {
        return current($this->dbData);
    }

    public function fetchAll() : array
    {
        return $this->dbData;
    }

    public function isSuccess() : bool
    {
        return empty($this->errors);
    }

    public function getErrors() : array
    {
        return $this->errors;
    }
}