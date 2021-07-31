<?php

namespace Lib\Crypto;

interface ICrypto
{
    public function __construct(string $alg);

    public function cryptInfo(string $data) : string;

    public function getAlgCrypt() : string;
}