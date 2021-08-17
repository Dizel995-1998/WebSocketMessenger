<?php

namespace Lib\Response;

class ServerInternalError extends HttpErrorException
{
    public function getHttpErrorCode(): int
    {
        return 500;
    }
}