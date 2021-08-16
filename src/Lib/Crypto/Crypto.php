<?php

namespace Lib\Crypto;

class Crypto implements ICrypto
{
    private string $alg;

    // TODO прокинуть ключ снаружи
     const SECRET_KEY = 'javainuse-secret-key';

    public function __construct(string $alg)
    {
        $alg = mb_strtolower($alg);

        if (!$this->checkSupportAlgHash($alg)) {
            throw new \RuntimeException(sprintf('Dont support alg %s hash', $alg));
        }

        $this->alg = $alg;
    }

    protected function checkSupportAlgHash(string $alg) : bool
    {
        return isset(array_flip(hash_algos())[$alg]);
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