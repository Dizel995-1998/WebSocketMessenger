<?php

namespace Entity;

use Lib\Database\Relations\OneToMany;
use Lib\Database\Relations\OneToOne;
use Lib\Database\Column\StringColumn;

/**
 * @Table({"name":"users"})
 */
class User implements \JsonSerializable
{
    /**
     * @IntegerColumn ({"isPrimaryKey":true})
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @ORM\StringColumn({"name":"name"})
     * @var string
     */
    protected string $name;

    /**
     * @StringColumn({"name":"external_id"})
     * @var null|string
     */
    protected ?string $externalId = null;

    /**
     * @StringColumn({"name":"picture_url"})
     * @var null|string
     */
    protected ?string $pictureUrl = null;

    /**
     * @StringColumn({"name":"status"})
     * @var null|string
     */
    protected ?string $status = null;

    /**
     * @StringColumn({"name":"password_hash"})
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * @StringColumn({"name":"login"})
     * @var string
     */
    protected string $login;

    /**
     * @OneToOne({"name":"picture_id", "targetEntity":"Picture", "mappedBy":"id"})
     * @var
     */
    protected $picture;

    /**
     * @OneToMany({"targetEntity":"Picture", "mappedBy":"user_id"})
     * @var
     */
    protected $pictures;

    public function getPicture()
    {
        return $this->picture;
    }

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

    public function setLogin(string $login) : self
    {
        $this->login = $login;
        return $this;
    }

    public function getLogin() : string
    {
        return $this->login;
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
        return $this->pictures;
    }

    // fixme: вызвать сервис провайдер и получить сервис хеширования
    public function setPassword(string $password) : self
    {
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