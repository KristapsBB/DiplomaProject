<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;

class Errors extends Controller
{
    public function page404()
    {
        return $this->showView('error', [
            'error' => 'Page not found'
        ], [], 404);
    }
}
