<?php

namespace DiplomaProject\Core\Modules;

class Http
{
    public $base_url = '';

    public function configure(array $params)
    {
        $this->setBaseUrl($params['base_url']);
    }

    private function setBaseUrl(string $base_url)
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

    public function isPost(): bool
    {
        return ('POST' === $_SERVER['REQUEST_METHOD']);
    }

    public function isGet(): bool
    {
        return ('GET' === $_SERVER['REQUEST_METHOD']);
    }

    /**
     * get var from $_GET by $var_name
     */
    public function get(string $var_name)
    {
        return $_GET[$var_name] ?? null;
    }

    /**
     * get var from $_POST by $var_name
     */
    public function post(string $var_name)
    {
        return $_POST[$var_name] ?? null;
    }

    public function setCookie(string $name, $value, int $expires = 0): bool
    {
        return setcookie($name, $value, $expires);
    }

    public function getCookie(string $name)
    {
        $value = null;

        if (array_key_exists($name, $_COOKIE)) {
            $value = $_COOKIE[$name];
        }

        return $value;
    }
}
