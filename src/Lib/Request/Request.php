<?php

namespace Lib\Request;

class Request extends \GuzzleHttp\Psr7\Request
{
    protected array $requestData;

    public function __construct(string $method, $uri, array $requestData, array $headers = [], $body = null, string $version = '1.1')
    {
        parent::__construct($method, $uri, $headers, $body, $version);
        $this->requestData = $requestData;
    }

    public function get(?string $key = null)
    {
        return $key ?
            $this->requestData[$key] :
            $this->requestData;
    }
}