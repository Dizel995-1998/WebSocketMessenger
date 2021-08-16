<?php

namespace Lib\Response;


use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

class BadRequest extends AbstractError
{
    public function getErrorCode(): int
    {
        return 400;
    }

    public function getErrorStatus(): string
    {
        return 'Bad Request';
    }
}