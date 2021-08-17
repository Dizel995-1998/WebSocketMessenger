<?php

namespace Lib\Response;

class Unauthorized extends HttpErrorException
{
    public function getHttpErrorCode(): int
    {
        return 401;
    }
}