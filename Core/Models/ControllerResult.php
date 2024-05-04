<?php

namespace DiplomaProject\Core\Models;

class ControllerResult
{
    protected string $type = 'result';
    public ?array $params = [];
    public int $http_code = 500;
    public $is_user_logged_in = false;

    public function getType(): string
    {
        return $this->type;
    }
}
