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
    public int $code;

    public function configure(array $config_params)
    {
        $this->setLayout($config_params['layout_path']);
        $this->setMaxNestingDepth($config_params['max_nesting_depth']);
    }

    public function setLayout(string $layout_path)
    {
        $this->layout_path = $layout_path;
    }

    public function setMaxNestingDepth(int $max_nesting_depth)
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
    public function showView(string $view_name, array $params, int $code = 200)
    {
        if ($this->curr_nesting_depth > $this->max_nesting_depth) {
            throw new \Exception('maximum recursion depth exceeded');
        }

        $this->pushParams();
        $this->curr_nesting_depth++;

        $this->params = $params;
        $this->code = $code;

        require($this->getPathToView($view_name));

        $this->curr_nesting_depth--;
        $this->popParams();
    }

    public function showLayout(string $view_name, array $params, int $code = 200)
    {
        $this->params = $params;
        $this->code = $code;

        ob_start();
        $this->showView($view_name, $params, $code);
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
}
