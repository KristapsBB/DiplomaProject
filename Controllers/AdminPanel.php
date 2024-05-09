<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Models\TenderSearch;

class AdminPanel extends Controller
{
    public function before(string $method_name)
    {
        if (!$this->isAdmin()) {
            return $this->showView('error', ['error' => 'Access denied'], [], 403);
        }

        return null;
    }

    public function importTenders()
    {
        $http = Core::getCurrentApp()->getHttp();

        $search_query = trim($http->get('search-query'));
        $page = $http->get('page') ?? 1;
        $page = (int) $page;

        $tender_search = new TenderSearch();

        if (null !== $search_query) {
            $tender_search->fetchTendersFromApi($search_query, $page);
        }

        return $this->showView('tender-import', [
            'search' => $tender_search,
            'tender_list' => $tender_search->getTenderList(),
        ]);
    }
}
