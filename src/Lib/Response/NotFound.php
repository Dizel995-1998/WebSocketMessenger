<?php

namespace Lib\Response;

class NotFound extends HttpErrorException
{
    public function getHttpErrorCode(): int
    {
        return 404;
    }
}