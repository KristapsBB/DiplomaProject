<?php

namespace DiplomaProject\Core;

use DiplomaProject\Core\Models\UrlParams;
use DiplomaProject\Core\Models\ViewParams;

class Controller
{
    public function showView(
        string $view_name,
        array $params = [],
        array $page_params = [],
        int $http_code = 200
    ): ViewParams {
        $view_params = new ViewParams($view_name, $http_code);
        $view_params->params = $params ?? [];
        $view_params->page_params = $page_params ?? [];

        return $view_params;
    }

    public function toUrl(
        string $uri,
        array $get_params = [],
        int $http_code = 303
    ): UrlParams {
        $url_params = new UrlParams($uri, $http_code);
        $url_params->params = $get_params;

        return $url_params;
    }
}
