<?php

namespace Lib\Jwt;

use InvalidArgumentException;
use JsonException;
use Lib\Container\Container;
use Lib\Crypto\ICrypto;
use RuntimeException;

class JwtToken
{
    private ICrypto $crypt;
    private array $payload;

    const TIME_TOKEN_LIVE_DAYS = 5;

    public function __construct(ICrypto $crypt, array $payload = [])
    {
        $this->crypt = $crypt;
        $this->payload = $payload;
    }

    private function generateHeader() : string
    {
        return base64url_encode(json_encode([
            'alg' => $this->crypt->getAlgCrypt(),
            'expires_in' => (new \DateTime(sprintf('+%d day', self::TIME_TOKEN_LIVE_DAYS)))->getTimestamp()
        ]));
    }

    private function generateSigner(string $header, string $payload) : string
    {
        return base64url_encode($this->crypt->cryptInfo($header . '.' . $payload));
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload() : array
    {
        return $this->payload;
    }

    public function __toString()
    {
        $header = $this->generateHeader();
        $payload = base64url_encode(json_encode($this->payload));
        return $header . '.' . $payload . '.' . $this->generateSigner($header, $payload);
    }

    /**
     * @throws JsonException|\ReflectionException
     */
    public static function parse(string $jwtToken) : self
    {
        $arTokenParts = explode('.', $jwtToken);

        if (count($arTokenParts) != 3) {
            throw new RuntimeException('Token must consist three parts');
        }

        if (!$header = ($arTokenParts[0])) {
            throw new InvalidArgumentException('Invalid jwt header json');
        }

        if (!$payload = ($arTokenParts[1])) {
            throw new InvalidArgumentException('Invalid jwt payload json');
        }

        if (!$signature = ($arTokenParts[2])) {
            throw new InvalidArgumentException('Invalid jwt signature json');
        }


        if (!$arPayload = json_decode(base64_decode($payload), true)) {
            throw new JsonException('Invalid jwt json payload');
        }

        if (!json_decode(base64_decode($header), true)['alg']) {
            throw new InvalidArgumentException('Header dont have alg key');
        }

        $crypt = Container::getService(ICrypto::class);
        $signatureHash = base64url_encode($crypt->cryptInfo($header . '.' . $payload));

        if ($signatureHash != $signature) {
            throw new InvalidArgumentException('Invalid signature');
        }

        return new self(Container::getService(ICrypto::class), $arPayload);
    }
}