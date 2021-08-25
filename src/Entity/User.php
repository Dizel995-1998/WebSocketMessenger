<?php

namespace Entity;

use Lib\Database\ArrayCollection;
use Lib\Database\DataManager;
use Lib\Database\Interfaces\IConnection;

class User extends DataManager
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

    /**
     * @ORM type varchar(50)
     * @ORM column_name LOGIN
     * @var string
     */
    protected string $login;

    /**
     * @ORM type varchar(100)
     * @ORM column_name PASSWORD_HASH
     * @var string
     */
    protected string $passwordHash;

    /**
     * @ORM type varchar(100)
     * @ORM column_name PICTURE_URL
     * @var string|null
     */
    protected ?string $pictureUrl;

    public function getUserGroups() : ArrayCollection
    {
        $conn = \Lib\Container\Container::getService(IConnection::class);
        $db = $conn->query(sprintf(
            'SELECT b_user_group.* FROM b_user_group
                JOIN b_users_groups ON b_users_groups.GROUP_ID = b_user_group.ID
                JOIN b_user ON b_users_groups.USER_ID = b_user.ID WHERE USER_ID = %d', $this->id)
        );

        $collection = new ArrayCollection();

        while ($tmp = $db->fetch()) {
            $collection->addElement($tmp);
        }

        return $collection;
    }

    public static function getTableName(): string
    {
        return 'b_user';
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    public function getPasswordHash() : string
    {
        return $this->passwordHash;
    }

    public function getPictureUrl() : string
    {
        return $this->pictureUrl;
    }

    public function setPictureUrl(string $pictureUrl) : self
    {
        $this->pictureUrl = $pictureUrl;
        return $this;
    }

    public function setLogin(string $login) : self
    {
        $this->login = $login;
        return $this;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function setPasswordHash(string $passwordHash) : self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }
}