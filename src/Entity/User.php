<?php

namespace Entity;

use Lib\Database\Collection\LazyCollection;
use Lib\Database\Relations\OneToMany;

/**
 * @ORM\Table({"name":"users"})
 */
class User implements \JsonSerializable
{
    /**
     * @ORM\StringColumn({"name":"NAME"})
     * @var string
     */
    protected string $name;

    /**
     * @ORM\StringColumn({"name":"LAST_NAME"})
     * @var string
     */
    protected string $last_name;

    /**
     * @ORM\IntegerColumn({"name":"ID"})
     * @var int|null
     */
    protected ?int $id;

//    /**
//     * @ORM\OneToMany({"sourceColumn":"ID", "sourceTable":"users", "targetColumn":"user_id", "targetTable":"pictures", "targetClassName":"Picture"})
//     * @var
//     */
//    protected $pictures;

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function setLastName(string $lastName) : self
    {
        $this->last_name = $lastName;
        return $this;
    }

    public function getLastName() : ?string
    {
        return $this->last_name;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getPictures()
    {
        return new LazyCollection(
            new OneToMany('ID', 'users', 'USER_ID', 'pictures', User::class, Picture::class)
        );
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
        ];
    }
}