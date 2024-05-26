<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;

class IndexPage extends Controller
{
    public function default()
    {
        if (!$this->isCurrUserLoggedIn()) {
            return $this->showView('index-page');
        }

        return $this->toUrl('/tenders/import-tenders');
    }
}
