<?php

namespace Entity;

/**
 * todo: хорошо бы добавить поле TTL, чтобы агентом удалять старые токены
 * @ORM\Table({"name":"access_tokens"})
 */
class AccessToken
{
    /**
     * @ORM\IntegerColumn({"name":"id"})
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * @ORM\StringColumn({"name":"token"})
     * @var string
     */
    protected string $token;

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