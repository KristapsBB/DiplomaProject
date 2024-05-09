<?php

namespace DiplomaProject\Exceptions;

use RuntimeException;

class TendersApiException extends RuntimeException
{
    public function __construct(
        private int $http_code,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }
}
