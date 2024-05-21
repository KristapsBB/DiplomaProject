<?php

namespace DiplomaProject\Core\Modules;

class Router
{
    private string $error_controller = '';
    private string $index_controller = '';
    private array $controller_map = [];
    private string $current_route;

    private string $current_controller = '';
    private string $current_method = '';

    public function configure(array $params)
    {
        $this->setErrorController($params['error_controller']);
        $this->setIndexController($params['index_controller']);
        $this->setControllerMap($params['controller_map']);
    }

    public function setErrorController(string $error_controller)
    {
        if (!class_exists($error_controller)) {
            throw new \Exception("class '$error_controller' not found");
        }

        $this->error_controller = $error_controller;
    }

    public function setIndexController(string $index_controller)
    {
        if (!class_exists($index_controller)) {
            throw new \Exception("class '$index_controller' not found");
        }

        $this->index_controller = $index_controller;
    }

    public function getErrorController(): string
    {
        return $this->error_controller;
    }

    public function getIndexController(): string
    {
        return $this->index_controller;
    }

    public function setControllerMap(array $controller_map)
    {
        $this->controller_map = $controller_map;
    }

    public function setRoutePath(string $route)
    {
        if (empty($this->current_route)) {
            $this->current_route = $route;
        }
    }

    public function parseRoute()
    {
        $route = strtolower($this->current_route);

        if (empty($route) || '/' === $route) {
            $this->current_controller = $this->getIndexController();
            $this->current_method     = 'default';
            return;
        }

        if ('/' === $route[0]) {
            $route = substr($route, 1);
        }

        $uri_components = explode('/', $route);
        $controller_name = $uri_components[0];
        $method_name     = $uri_components[1] ?? '';

        if (!array_key_exists($controller_name, $this->controller_map)) {
            return;
        }

        $this->current_controller = $this->controller_map[$controller_name];

        if (!empty($method_name)) {
            $method_name = self::toCamelCase($method_name);
        } else {
            $method_name = 'default';
        }

        if (!method_exists($this->current_controller, $method_name)) {
            return;
        }

        $this->current_method = $method_name;
    }

    public function getController(): string
    {
        return $this->current_controller;
    }

    public function getMethod(): string
    {
        return $this->current_method;
    }

    /**
     * "admin-panel" => "AdminPanel"
     */
    public static function toPascalCase(string $kebab_case): string
    {
        $words = explode('-', $kebab_case);
        $words = array_map('strtolower', $words);
        $words = array_map('ucfirst', $words);
        $pascal_case = implode('', $words);

        return $pascal_case;
    }

    /**
     * "admin-panel" => "AdminPanel"
     */
    public static function toCamelCase(string $kebab_case): string
    {
        $camel_case = lcfirst(self::toPascalCase($kebab_case));

        return $camel_case;
    }
}
