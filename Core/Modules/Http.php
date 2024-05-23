<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Module;

class Http extends Module
{
    public $base_url = '';

    protected function setBaseUrl(string $base_url)
    {
        $this->base_url = $base_url;
    }

    /**
     * @param ?array $get_params GET-parameters of URL
     */
    public function generateUrl(string $uri, ?array $get_params = null)
    {
        $url = $uri;

        if (!empty($get_params)) {
            $url .= \http_build_query($get_params, 'var_');
        }

        if (parse_url($url, PHP_URL_HOST)) {
            return "{$url}";
        }

        return "http://{$this->base_url}$url";
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

    public function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }
}
