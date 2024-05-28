<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Core;
use DiplomaProject\Core\Module;

class Viewer extends Module
{
    private string $layout_path;
    private array $params_stack = [];
    private string $root_view_contents = '';
    /**
     * maximum nesting depth of views
     */
    private int $max_nesting_depth = 3;
    private int $curr_nesting_depth = 0;

    public array $page_params = [];
    public array $params = [];

    /**
     * HTTP code of response
     */
    public int $http_code = 500;

    public function initSystemAssets()
    {
        $asset_manager = Core::getCurrentApp()->getAssetManager();

        $asset_manager->registerStyle('system-normalize', 'css/normalize.css');
        $asset_manager->registerStyle('system-reset', 'css/my-reset.css');
        $asset_manager->registerStyle('system-stylecss', 'css/system-style.css', '1.1.0');
        $asset_manager->registerScript('system-mainjs', 'js/system-main.js', '1.1.0');

        $asset_manager->enqueueStyles([
            'system-normalize',
            'system-reset',
            'system-stylecss'
        ]);

        $asset_manager->enqueueScripts(['system-mainjs']);

        $this->onHead([$asset_manager, 'printEnqueuedStyles'], 'system-css');
        $this->onHead([$asset_manager, 'printEnqueuedScripts'], 'system-js');
    }

    protected function setLayoutPath(string $layout_path)
    {
        $this->layout_path = $layout_path;
    }

    public function changeLayout(string $layout_path)
    {
        $this->setLayoutPath($layout_path);
    }

    protected function setMaxNestingDepth(int $max_nesting_depth)
    {
        $this->max_nesting_depth = $max_nesting_depth;
    }

    private function pushParams()
    {
        array_push($this->params_stack, $this->params);
    }

    private function popParams()
    {
        $this->params = array_pop($this->params_stack);
    }

    private function setPageParam(string $param_name, $value)
    {
        $this->page_params[$param_name] = $value;
    }

    private function getPageParam(string $param_name)
    {
        return $this->page_params[$param_name] ?? null;
    }

    /**
     * returns HTTP code of response
     */
    private function getHttpCode(): int
    {
        return $this->http_code;
    }

    /**
     * prints HTTP code of response
     */
    private function printHttpCode()
    {
        echo $this->getHttpCode();
    }

    /**
     * used inside the layout to show the selected view
     */
    public function theRootView()
    {
        echo $this->root_view_contents;
    }

    /**
     * shows the view; recursively
     */
    public function showView(string $view_name, array $params = [])
    {
        if ($this->curr_nesting_depth > $this->max_nesting_depth) {
            throw new \Exception('maximum recursion depth exceeded');
        }

        $this->pushParams();
        $this->curr_nesting_depth++;

        $this->params = $params;

        require($this->getPathToView($view_name));

        $this->curr_nesting_depth--;
        $this->popParams();
    }

    public function showLayout(
        string $view_name,
        array $params = [],
        array $page_params = [],
        int $http_code = 200
    ) {
        $this->initSystemAssets();

        $this->page_params = $page_params;
        $this->http_code = $http_code;

        ob_start();
        $this->showView($view_name, $params);
        $this->root_view_contents = ob_get_contents();
        ob_end_clean();

        require($this->getPathToView($this->layout_path));
    }

    public function getPathToView(string $view_name): string
    {
        return Core::getCurrentApp()->getAppRoot() . "Views/{$view_name}.php";
    }

    public function onHead(callable $handler, string $group_name = 'default')
    {
        $this->on('head', $handler, $group_name);
    }

    /**
     * triggers the "head" event, used in the layout
     */
    private function head()
    {
        $this->trigger('head');
    }

    public function onFooter(callable $handler, string $group_name = 'default')
    {
        $this->on('footer', $handler, $group_name);
    }

    /**
     * triggers the "footer" event, used in the layout
     */
    private function footer()
    {
        $this->trigger('footer');
    }

    /**
     * sets CSS classes for the body tag, used in the layout
     */
    private function setBodyClass(string $css_classes = '')
    {
        $this->setPageParam('body-class', $css_classes);
    }

    /**
     * prints CSS classes for the body tag, used in the layout
     */
    private function printBodyClass()
    {
        echo $this->getPageParam('body-class');
    }
}
