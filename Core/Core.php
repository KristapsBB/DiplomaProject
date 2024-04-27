<?php

namespace DiplomaProject\Core;

use DiplomaProject\Core\Modules\Http;
use DiplomaProject\Core\Modules\Viewer;

/**
 * Singleton & Dependency Injection
 */
class Core
{
    private static self $current_app;
    private Viewer $viewer;
    private Http $http;

    private string $root;

    private function __construct()
    {
    }

    public static function getCurrentApp(): self
    {
        if (empty(self::$current_app)) {
            self::$current_app = new self();
        }

        return self::$current_app;
    }

    public function getAppRoot(): string
    {
        return $this->root;
    }

    private function configure()
    {
        $this->root = dirname(__DIR__) . '/';

        $this->http = new Http();
        $this->http->configure($_SERVER['SERVER_NAME']);

        $this->viewer = new Viewer();
    }

    public function getViewer(): Viewer
    {
        return $this->viewer;
    }

    public function getHttp(): Http
    {
        return $this->http;
    }

    private function processRequest()
    {
        /**
         * selecting controller and method
         */
        $controller_class = 'DiplomaProject\Controllers\Authentication';
        $method_name = 'login';

        if (!class_exists($controller_class) || !method_exists($controller_class, $method_name)) {
            return $this->getViewer()->showView('error', ['error' => 'Page not found'], 404);
        }

        /**
         * running the selected controller method
         */
        $controller = new $controller_class();
        $result = $controller->$method_name();

        /**
         * showing view, redirection, etc
         */
        switch ($result['type']) {
            case 'view':
                $view_name = $result['view_name'];
                $params    = $result['params'];
                $code      = $result['code'];

                return $this->getViewer()->showView($view_name, $params, $code);
                break;
            case 'url':
                $uri        = $result['uri'];
                $get_params = $result['get_params'];
                $code       = $result['code'];

                $url = $this->getHttp()->generateUrl($uri, $get_params);
                return $this->getHttp()->redirect($url, $code);
                break;
        }
    }

    public function run()
    {
        $this->configure();

        try {
            $this->processRequest();
        } catch (\Throwable $e) {
            echo $e->getMessage();
            var_dump($e->getTrace());
        }
    }
}
