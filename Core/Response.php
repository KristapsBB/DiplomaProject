<?php

namespace DiplomaProject\Core;

class Response
{
    public const TYPE_HTML = 'html';
    public const TYPE_JSON = 'json';
    public const TYPE_FILE = 'file';

    public function __construct(
        private string $type,
        private string $body = '',
        private array $headers = [],
        private int $http_code = 200,
    ) {
    }

    public function send()
    {
        foreach ($this->headers as $header) {
            header(
                $header['header'] ?? $header[0],
                $header['replace'] ?? $header[1] ?? true,
                $header['response_code'] ?? $header[2] ?? 0
            );
        }

        echo $this->body;
    }
}
