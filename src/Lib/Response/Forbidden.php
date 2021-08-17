<?php

namespace Lib\Response;

class Forbidden extends HttpErrorException
{
    public function getHttpErrorCode(): int
    {
        return 403;
    }
}