<?php

namespace Lib\Jwt;

use InvalidArgumentException;
use JsonException;
use Lib\Container\Container;
use Lib\Crypto\ICrypto;
use RuntimeException;

/**
 * TODO ввести свои виды исключений
 * fixme: необходим рефакторинг
 */
class JwtToken
{
    /**
     * Ключ в JWT токене
     * @var string
     */
    const TIME_TO_LIVE = 'expires_in';

    /**
     * Ключ в JWT токене
     * @var string
     */
    const ALG_TYPE = 'alg';

    /**
     * Ключ в JWT токене
     * @var string
     */
    const USER_ID = 'userId';

    /**
     * Обязательные ключи в секции payload
     * @var string
     */
    const REQUIRED_FIELDS_IN_PAYLOAD = [
        self::USER_ID
    ];

    const TIME_TOKEN_LIVE_DAYS = 5;

    private ICrypto $crypt;

    private array $payload;

    public function __construct(ICrypto $crypt, array $payload = [])
    {
        $this->crypt = $crypt;
        $this->payload = $payload;
    }

    /**
     * @throws \Exception
     */
    private function generateHeader() : string
    {
        return base64url_encode(json_encode([
            self::ALG_TYPE => $this->crypt->getAlgCrypt(),
            self::TIME_TO_LIVE => (new \DateTime(sprintf('+%d day', self::TIME_TOKEN_LIVE_DAYS)))->getTimestamp()
        ]));
    }

    private function generateSigner(string $header, string $payload) : string
    {
        return base64url_encode($this->crypt->cryptInfo($header . '.' . $payload));
    }

    public function setUserId(int $userId) : self
    {
        $this->setPayload([self::USER_ID => $userId]);
        return $this;
    }

    public function getUserId() : int
    {
        return $this->getPayloadByKey(self::USER_ID);
    }

    /**
     * @param string $key
     * @return string|int|null
     */
    protected function getPayloadByKey(string $key)
    {
        return $this->payload[$key];
    }

    protected function setPayload(array $payload)
    {
        $this->payload = array_merge($this->payload, $payload);
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

        if (!json_decode(base64_decode($header), true)[self::ALG_TYPE]) {
            throw new InvalidArgumentException('Header dont have alg key');
        }

        $crypt = Container::getService(ICrypto::class);
        $signatureHash = base64url_encode($crypt->cryptInfo($header . '.' . $payload));

        if ($signatureHash != $signature) {
            throw new InvalidArgumentException('Invalid signature');
        }

        foreach (self::REQUIRED_FIELDS_IN_PAYLOAD as $field) {
            if (!array_key_exists($field, $arPayload)) {
                throw new RuntimeException(sprintf('Missing required field "%s" in payload', $field));
            }
        }

        return new self(Container::getService(ICrypto::class), $arPayload);
    }
}