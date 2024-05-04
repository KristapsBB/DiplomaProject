<?php

namespace DiplomaProject\Core\Models;

class UrlParams extends ControllerResult
{
    public string $uri;

    public function __construct(string $uri, int $http_code = 303)
    {
        $this->type = 'url';
        $this->uri = $uri;
        $this->http_code = $http_code;
    }
}
