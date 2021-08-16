<?php

namespace Lib\Response;

class ValidationError extends AbstractError
{
    public function getErrorCode(): int
    {
        return 422;
    }

    public function getErrorStatus(): string
    {
        return 'Unprocessable Entity';
    }
}