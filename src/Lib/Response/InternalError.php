<?php

namespace Lib\Response;


class InternalError extends AbstractError
{
    public function getErrorCode(): int
    {
        return 500;
    }

    public function getErrorStatus(): string
    {
        return 'Server internal error';
    }
}