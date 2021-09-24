<?php

namespace Lib\Database\Column;

class IntegerColumn extends BaseColumn
{
    public function getType(): string
    {
        return 'integer';
    }
}