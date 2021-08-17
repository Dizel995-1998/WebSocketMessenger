<?php

namespace Lib\Response;

class BadRequest extends HttpErrorException
{
    public function getHttpErrorCode(): int
    {
        return 400;
    }
}