<?php

namespace Lib\Database\Column;

class StringColumn extends BaseColumn
{
    public function getType(): string
    {
        return 'string';
    }
}