<?php

namespace Entity;

use Lib\Database\Column as ORM;

/**
 * todo: хорошо бы добавить поле TTL, чтобы агентом удалять старые токены
 * @ORM\Table({"name":"access_tokens"})
 */
class AccessToken
{
    /**
     * @ORM\PrimaryKey ({"name":"id"})
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @ORM\StringColumn({"name":"token"})
     * @var string
     */
    protected string $token;

    /**
     * @ORM\IntegerColumn ({"name":"user_id"})
     * @var int
     */
    protected int $userId;

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId) : self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getToken() : string
    {
        return $this->token;
    }

    public function setToken(string $token) : self
    {
        $this->token = $token;
        return $this;
    }
}