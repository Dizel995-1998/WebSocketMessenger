<?php

namespace Entity;

use Lib\Database\DataManager;

class User extends DataManager
{
    /**
     * @ORM primary_key ID
     * @ORM type int
     * @ORM column_name ID
     * @var int|null
     */
    public ?int $id = null;

    /**
     * @ORM type varchar(50)
     * @ORM column_name NAME
     * @var string
     */
    public string $name;

    /**
     * @ORM type varchar(50)
     * @ORM column_name LOGIN
     * @var string
     */
    public string $login;

    /**
     * @ORM type varchar(100)
     * @ORM column_name PASSWORD_HASH
     * @var string
     */
    public string $passwordHash;

    /**
     * @ORM type varchar(100)
     * @ORM column_name PICTURE_URL
     * @var string|null
     */
    public ?string $pictureUrl;

    public static function getTableName(): string
    {
        return 'b_user';
    }
}