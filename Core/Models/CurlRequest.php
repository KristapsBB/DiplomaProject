<?php

namespace DiplomaProject\Core\Models;

use DiplomaProject\Core\Enums\HttpMethod;

class CurlRequest
{
    private \CurlHandle $curl_handle;
    private HttpMethod $http_method = HttpMethod::Post;
    private array $http_headers = [];
    private string $body = '';
    private string $last_result;

    public function __construct()
    {
        $new_curl_handle = curl_init();

        if (false === $new_curl_handle) {
            throw new \RuntimeException('fail curl init');
        }

        $this->curl_handle = $new_curl_handle;
    }

    public function setHeader(string $header_name, string $value)
    {
        $this->http_headers[$header_name] = $value;
    }

    private function prepareHeaders(): array
    {
        $prepared_headers = [];

        foreach ($this->http_headers as $header_name => $value) {
            $prepared_headers[] = "$header_name: $value";
        }

        return $prepared_headers;
    }

    public function setHttpMethod(HttpMethod | string $http_method)
    {
        if (\is_string($http_method)) {
            $http_method = strtoupper($http_method);
            $http_method = HttpMethod::from($http_method);
        }

        $this->http_method = $http_method;
    }

    /**
     * sets the body of the POST request
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    public function send(string $url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception('invalid URL');
        }

        $headers = $this->prepareHeaders();

        if (count($headers) > 0) {
            curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $headers);
        }

        if (HttpMethod::Post === $this->http_method) {
            curl_setopt($this->curl_handle, CURLOPT_POST, true);
        }

        curl_setopt($this->curl_handle, CURLOPT_URL, $url);
        curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $this->body);
        curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl_handle);

        if (false === $result) {
            $this->last_result = '';
        } else {
            $this->last_result = $result;
        }
    }

    public function getResponse(): CurlResponse
    {
        return (new CurlResponse(
            $this->curl_handle,
            $this->last_result
        ));
    }
}
