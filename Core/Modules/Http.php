<?php

namespace DiplomaProject\Core\Modules;

class Http
{
    public $base_url = '';

    public function configure(string $base_url)
    {
        $this->base_url = $base_url;
    }

    public function generateUrl(string $uri, array $get_params)
    {
        $url = '';
        $url .= $uri;

        if (!empty($get_params)) {
            $url .= self::generateQueryString($get_params);
        }

        return "http://{$this->base_url}$url";
    }

    public static function generateQueryString(array $get_params): string
    {
        $query_string = '';

        $get_paras = array_map(
            function ($key, $value) {
                return "{$key}={$value}";
            },
            $get_params
        );

        $query_string .= implode('&', $get_paras);

        return "?{$query_string}";
    }

    public function redirect(string $url, int $code = 303)
    {
        header("Location: {$url}", true, $code);
    }
}
