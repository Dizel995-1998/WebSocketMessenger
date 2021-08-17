<?php

namespace Lib\Response;

class SuccessResponse extends JsonResponse
{
    const HTTP_STATUS_OK = 200;

    public function __construct($body = null, array $headers = [], string $version = '1.1', string $reason = null)
    {
        parent::__construct(self::HTTP_STATUS_OK, ['success' => true, 'data' => $body], $headers, $version, $reason);
    }
}