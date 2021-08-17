<?php

namespace Lib\Response;

use Throwable;

abstract class HttpErrorException extends \Exception
{
    /**
     * @param string|array $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(is_array($message) ? serialize($message) : $message, $code, $previous);
    }

    /**
     * Возвращает код ответа
     * @return int
     */
    abstract public function getHttpErrorCode() : int;
}