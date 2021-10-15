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
     * @ORM\IntegerColumn({"name":"id"})
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @ORM\StringColumn({"name":"name"})
     * @var string
     */
    protected string $name;

    /**
     * @ORM\StringColumn({"name":"external_id"})
     * @var null|string
     */
    protected ?string $externalId = null;

    /**
     * @ORM\StringColumn({"name":"picture_url"})
     * @var null|string
     */
    protected ?string $pictureUrl = null;

    /**
     * @ORM\StringColumn({"name":"status"})
     * @var null|string
     */
    protected ?string $status = null;

    /**
     * @ORM\StringColumn({"name":"password_hash"})
     * @var string|null
     */
    protected ?string $password = null;

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

    public function setPictureUrl(string $pictureUrl)
    {
        $this->pictureUrl = $pictureUrl;
        return $this;
    }

    public function getPictureUrl() : ?string
    {
        return $this->pictureUrl;
    }

    public function getExternalId() : ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId) : self
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function setStatus(string $status) : self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus() : ?string
    {
        return $this->status;
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

    public function setPassword(string $password) : self
    {
        // fixme: вызвать сервис провайдер и получить сервис хеширования
        // todo: или применить observer и сервис слушателей
        $this->password = $password;
        return $this;
    }

    public function jsonSerialize()
    {
        $res = [];

        $reflection = new \ReflectionClass(static::class);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            $property->setAccessible(true);
            $res[$property->getName()] = $property->getValue($this);
        }

        return $res;
    }
}