<?php

namespace Lib\Response;

use GuzzleHttp\Psr7\Response;

class JsonResponse extends Response
{
    const CONTENT_TYPE = 'application/json';

    public function __construct(
        $body = null,
        int $status = 200,
        array $headers = [],
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, array_merge($headers, ['Content-Type' => self::CONTENT_TYPE]), json_encode($body), $version, $reason);
    }
}