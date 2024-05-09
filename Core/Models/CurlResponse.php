<?php

namespace DiplomaProject\Core\Models;

class CurlResponse
{
    public function __construct(
        private \CurlHandle $curl_handle,
        private string $result,
    ) {
    }

    public function rawText(): string
    {
        return $this->result;
    }

    public function json(): array
    {
        return \json_decode($this->result, true);
    }

    public function httpCode()
    {
        return \curl_getinfo($this->curl_handle, CURLINFO_RESPONSE_CODE);
    }
}
