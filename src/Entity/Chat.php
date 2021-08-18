<?php

namespace Entity;

use Lib\Database\DataManager;

class Chat extends DataManager
{
    /**
     * @ORM primary_key ID
     * @ORM column_name ID
     * @var int|null
     */
    public ?int $id = null;

    public string $name;

    public static function getTableName(): string
    {
        return 'chat';
    }
}