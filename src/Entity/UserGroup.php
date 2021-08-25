<?php

namespace Entity;

use Lib\Database\DataManager;

class UserGroup extends DataManager
{
    /**
     * @ORM primary_key ID
     * @ORM type int
     * @ORM column_name ID
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @ORM type varchar(50)
     * @ORM column_name NAME
     * @var string
     */
    protected string $name;

    public static function getTableName(): string
    {
        return 'b_user_group';
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }
}