<?php

namespace DiplomaProject\Core\Models;

class ViewParams extends ControllerResult
{
    public string $view_name;
    public ?array $page_params = [];

    public function __construct(string $view_name, int $http_code = 200)
    {
        $this->type = 'view';
        $this->view_name = $view_name;
        $this->http_code = $http_code;
    }
}
