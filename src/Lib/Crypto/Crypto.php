<?php

namespace Lib\Crypto;

class Crypto implements ICrypto
{
    private string $alg;

    // TODO прокинуть ключ снаружи
    const SECRET_KEY = 'askdskakadskasdkaskdkdasldkasl';

    public function __construct(string $alg)
    {
        // TODO проверка поддерживаемости алгоритма хеширования
        $this->alg = $alg;
    }

    public function cryptInfo(string $data): string
    {
        return hash_hmac($this->alg, $data, self::SECRET_KEY);
    }

    public function getAlgCrypt(): string
    {
        return $this->alg;
    }
}