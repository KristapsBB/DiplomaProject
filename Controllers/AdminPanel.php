<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Models\Pagination;
use DiplomaProject\Models\Tender;
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

        $mode = $http->get('mode') ?? '';
        $tender_search = new TenderSearch();

        if (null !== $search_query) {
            $tender_search->setMode($mode);
            $tender_search->fetchTendersFromApi($search_query, $page);
        }

        $pagination = new Pagination(
            $page,
            $tender_search->countPages() ?? 0,
            "?search-query={$search_query}&mode={$mode}&page="
        );

        return $this->showView('tender-import', [
            'search' => $tender_search,
            'tender_list' => $tender_search->getTenderList(),
            'pagination' => $pagination,
        ]);
    }

    public function getTenderTemplate()
    {
        $tender = Tender::getStub();
        $tender_fields = $tender->getFields();

        foreach ($tender_fields as $field_name => $value) {
            $tender_fields[$field_name] = '%' . strtoupper($field_name) . '%';
        }

        Core::getCurrentApp()->getViewer()->setLayout('empty-layout');

        return $this->showView('tender-list-item', [
            'tender' => $tender_fields,
        ]);
    }

    public function getTendersData()
    {
        $http = Core::getCurrentApp()->getHttp();

        $search_query = trim($http->get('search-query'));
        $page = (int) ($http->get('page') ?? 1);
        $mode = $http->get('mode') ?? '';

        $tender_search = new TenderSearch();
        $tender_search->setMode($mode);
        $tender_search->fetchTendersFromApi($search_query, $page);

        $tenders = $tender_search->getTenderList()->getTenders();

        foreach ($tenders as $key => $tender) {
            $tenders[$key] = $tender->getFields();
        }

        $response = [
            'error' => $tender_search->getLastError(),
            'items' => $tenders,
        ];

        return $this->sendJson($response);
    }
}
