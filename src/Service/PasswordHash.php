<?php

namespace Service;

use Lib\Crypto\ICrypto;

class PasswordHash
{
    protected ICrypto $crypto;

    // todo переименовать ICrypto в какой нибудь IHasher
    public function __construct(ICrypto $crypto)
    {
        $this->crypto = $crypto;
    }

    /**
     * Хеширует пароль и возвращает его хеш
     * @param string $password
     * @return string
     */
    public function hashPassword(string $password) : string
    {
        return $this->crypto->cryptInfo($password);
    }
}