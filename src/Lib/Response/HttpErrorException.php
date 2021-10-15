<?php

namespace Lib\Response;

use Throwable;

abstract class HttpErrorException extends \Exception
{
    protected string $error;
    /**
     * @param string|array $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->error = is_array($message) ? serialize($message) : $message;
        parent::__construct($this->error, $code, $previous);
    }

    public function getHttpError() : string|array
    {
        return unserialize($this->error) ?: $this->error;
    }

    /**
     * Возвращает код ответа
     * @return int
     */
    abstract public function getHttpErrorCode() : int;
}