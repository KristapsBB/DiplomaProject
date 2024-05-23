<?php

namespace DiplomaProject\Core;

use DiplomaProject\Core\Response;

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

    /**
     * returns path to application root (with trailing slash)
     */
    public function getAppRoot(): string
    {
        return $this->root;
    }

    private function configure(string $config_name)
    {
        $this->root = dirname(__DIR__) . '/';

        $app_configs = require_once($this->root . 'app-configuration.php');
        $current_config = $app_configs[$config_name];

        if (array_key_exists('logger', $current_config)) {
            $logger_conf = $current_config['logger'];
            unset($current_config['logger']);

            $current_config = array_merge(
                ['logger' => $logger_conf],
                $current_config
            );
        }

        foreach ($current_config as $module_name => $module_config) {
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

        self::info('Core is configured');
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

    /**
     * returns logger module
     * @return \DiplomaProject\Core\Modules\Logger
     */
    public function getLogger()
    {
        return $this->getModule('logger');
    }

    public static function error(string $message)
    {
        self::getCurrentApp()->getLogger()?->error($message);
    }

    public static function warning(string $message)
    {
        self::getCurrentApp()->getLogger()?->warning($message);
    }

    public static function info(string $message)
    {
        self::getCurrentApp()->getLogger()?->info($message);
    }

    public static function debug(string $message)
    {
        self::getCurrentApp()->getLogger()?->debug($message);
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
            $controller_class = $this->getRouter()->getErrorController();
            $method_name = 'page404';
        }

        /**
         * running the selected controller method
         */
        $controller = new $controller_class();
        $response = null;

        if (method_exists($controller, 'before')) {
            /**
             * @var ?Response $response
             */
            $response = $controller->before($method_name);
        }

        if (null === $response) {
            /**
             * @var ?Response $response
             */
            $response = $controller->$method_name();
        }

        /**
         * showing view, redirection, etc
         */
        $response?->send();
    }

    public function run(string $config_name = 'default')
    {
        $this->configure($config_name);

        try {
            $this->processRequest();
        } catch (\Throwable $e) {
            Core::error($e->getMessage());
            Core::error($e->getTraceAsString());
            echo $e->getMessage();
            var_dump($e);
        }
    }
}
