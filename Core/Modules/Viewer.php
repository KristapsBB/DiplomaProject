<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Core;

class Viewer
{
    public array $params = [];
    public int $code;

    public function showView(string $view_name, array $params, int $code = 200)
    {
        $this->params = $params;
        $this->code = $code;

        require_once(Core::getCurrentApp()->getAppRoot() . "Views/{$view_name}.php");
    }
}
