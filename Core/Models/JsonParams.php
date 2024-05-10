<?php

namespace DiplomaProject\Core\Models;

class JsonParams extends ControllerResult
{
    public function __construct(public array $data, int $http_code = 200)
    {
        $this->type = 'json';
        $this->http_code = $http_code;
    }
}
