<?php

namespace Entity;

use Lib\Database\DataManager;

class UserTable extends DataManager
{
    /**
     * @ORM primary_key ID
     * @ORM column_name ID
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @ORM column_name NAME
     * @var string
     */
    public string $name;

    /**
     * @ORM column_name LOGIN
     * @var string
     */
    public string $login;

    /**
     * @ORM column_name PASSWORD_HASH
     * @var string
     */
    public string $passwordHash;

    /**
     * @ORM column_name PICTURE_URL
     * @var string|null
     */
    public ?string $pictureUrl;

    public static function getTableName(): string
    {
        return 'b_user';
    }
}