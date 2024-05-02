<?php

namespace DiplomaProject\Core;

/**
 * Singleton & Dependency Injection
 */
class Core
{
    private static self $current_app;
    private array $modules = [];

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

    private function configure(string $config_name)
    {
        $this->root = dirname(__DIR__) . '/';

        $app_config = require_once($this->root . 'app-configuration.php');

        foreach ($app_config[$config_name] as $module_name => $module_config) {
            $module_class = $module_config['class'] ?? '';

            if (empty($module_class) || !is_string($module_class) || !class_exists($module_class)) {
                throw new \Exception(
                    "class '$module_class' not found in app-configuration to the module '$module_name'"
                );
            }

            $module = new $module_class();
            $module->configure($module_config['params'] ?? []);
            $this->modules[$module_name] = $module;
        }
    }

    private function getModule(string $module_name)
    {
        if (!array_key_exists($module_name, $this->modules)) {
            return null;
        }

        $r = $this->modules[$module_name];

        return $r;
    }

    /**
     * returns viewer module
     * @return \DiplomaProject\Core\Modules\Viewer
     */
    public function getViewer()
    {
        return $this->getModule('viewer');
    }

    /**
     * returns http module
     * @return \DiplomaProject\Core\Modules\Http
     */
    public function getHttp()
    {
        return $this->getModule('http');
    }

    /**
     * returns security module
     * @return \DiplomaProject\Core\Modules\Security
     */
    public function getSecurity()
    {
        return $this->getModule('security');
    }

    /**
     * returns db module
     * @return \DiplomaProject\Core\Modules\DataBase
     */
    public function getDb()
    {
        return $this->getModule('db');
    }

    /**
     * returns authentication module
     * @return \DiplomaProject\Core\Modules\Authentication
     */
    public function getAuthentication()
    {
        return $this->getModule('authentication');
    }

    /**
     * returns router module
     * @return \DiplomaProject\Core\Modules\Router
     */
    public function getRouter()
    {
        return $this->getModule('router');
    }

    /**
     * returns asset-manager module
     * @return \DiplomaProject\Core\Modules\AssetManager
     */
    public function getAssetManager()
    {
        return $this->getModule('asset-manager');
    }

    private function processRequest()
    {
        /**
         * parsing the request URL to select controller and method
         */
        $uri = str_replace("?{$_SERVER['QUERY_STRING']}", '', $_SERVER['REQUEST_URI']);

        $this->getRouter()->setRoutePath($uri);
        $this->getRouter()->parseRoute();
        $controller_class = $this->getRouter()->getController();
        $method_name      = $this->getRouter()->getMethod();

        if (!class_exists($controller_class) || !method_exists($controller_class, $method_name)) {
            return $this->getViewer()->showLayout('error', ['error' => 'Page not found'], 404);
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

                return $this->getViewer()->showLayout($view_name, $params, $code);
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

    public function run(string $config_name = 'default')
    {
        $this->configure($config_name);

        try {
            $this->processRequest();
        } catch (\Throwable $e) {
            echo $e->getMessage();
            var_dump($e);
        }
    }
}
