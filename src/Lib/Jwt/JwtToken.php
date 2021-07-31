<?php

namespace Lib\Jwt;

use JsonException;
use Lib\Crypto\Crypto;
use Lib\Crypto\ICrypto;
use RuntimeException;

class JwtToken
{
    private ICrypto $crypt;
    private array $payload;

    public function __construct(array $arData, ICrypto $crypt)
    {
        $this->crypt = $crypt;
        $this->payload = $arData;
    }

    private function generateHeader() : string
    {
        return base64url_encode(json_encode(['alg' => $this->crypt->getAlgCrypt(), 'typ' => 'JWT']));
    }

    public function getPayload() : array
    {
        return $this->payload;
    }

    private function generateSigner(string $header, string $payload) : string
    {
        return base64url_encode($this->crypt->cryptInfo($header . '.' . $payload));
    }

    public function appendToPayload(array $appendToPayload)
    {
        $this->payload = array_merge($this->payload, $appendToPayload);
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    public function __toString()
    {
        $header = $this->generateHeader();
        $payload = base64url_encode(json_encode($this->payload));
        return $header . '.' . $payload . '.' . $this->generateSigner($header, $payload);
    }

    /**
     * @throws JsonException
     */
    public static function parse(string $jwtToken) : self
    {
        $arTokenParts = explode('.', $jwtToken);

        if (count($arTokenParts) != 3) {
            throw new RuntimeException('невалидный токен, токен должен содержать 3 части');
        }

        if (!$payload = json_decode(base64url_decode($arTokenParts[1]), true)) {
            throw new JsonException('Invalid payload json');
        }

        if (!$alg = json_decode(base64url_decode($arTokenParts[0]), true)['alg']) {
            throw new JsonException('Header dont have alg key');
        }

        return new self($payload, new Crypto($alg));
    }
}