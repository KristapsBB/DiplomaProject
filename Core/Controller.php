<?php

namespace DiplomaProject\Core;

class Controller
{
    public function showView(string $view_name, array $params, int $code = 200): array
    {
        return [
            'type' => 'view',
            'view_name' => $view_name,
            'params' => $params,
            'code' => $code
        ];
    }

    public function toUrl(string $uri, array $get_params = [], int $code = 303)
    {
        return [
            'type' => 'url',
            'uri' => $uri,
            'get_params' => $get_params,
            'code' => $code,
        ];
    }
}
