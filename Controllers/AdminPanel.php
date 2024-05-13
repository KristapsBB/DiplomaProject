<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Enums\TenderSearchMode;
use DiplomaProject\Models\Pagination;
use DiplomaProject\Models\Tender;
use DiplomaProject\Models\TenderList;
use DiplomaProject\Models\TenderSearch;

class AdminPanel extends Controller
{
    public function before(string $method_name)
    {
        /**
         * names of methods that can only be accessed by a POST request
         */
        $onlyPost = ['searchAndSave', 'deleteTenders'];

        if (!$this->isAdmin()) {
            return $this->showView('error', ['error' => 'Access denied'], [], 403);
        }

        $http = Core::getCurrentApp()->getHttp();

        if (false !== array_search($method_name, $onlyPost) && !$http->isPost()) {
            return $this->showView('error', ['error' => 'Method Not Allowed'], [], 405);
        }

        return null;
    }

    public function importTenders()
    {
        $http = Core::getCurrentApp()->getHttp();

        $search_query = trim($http->get('search-query'));
        $page = (int) ($http->get('page') ?? 1);
        $mode = $http->get('mode') ?? '';

        $tender_search = new TenderSearch($mode);

        if (null !== $search_query) {
            $tender_search->fetchTendersFromApi($search_query, $page);
        }

        $pagination = new Pagination(
            $page,
            $tender_search->countPages(),
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
        $tender_fields = Tender::getStub()->getFields();

        foreach (array_keys($tender_fields) as $field_name) {
            $tender_fields[$field_name] = '%' . strtoupper($field_name) . '%';
        }

        Core::getCurrentApp()->getViewer()->setLayout('empty-layout');

        return $this->showView('tender-list-item', [
            'tender' => $tender_fields,
            'item_data' => [
                'editing_mode' => 'saving',
                'is_saved' => false
            ],
        ]);
    }

    public function getTendersData()
    {
        $http = Core::getCurrentApp()->getHttp();

        $search_query = trim($http->get('search-query'));
        $page = (int) ($http->get('page') ?? 1);
        $mode = $http->get('mode') ?? '';

        $tender_search = new TenderSearch($mode);
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

    public function searchAndSave()
    {
        $http = Core::getCurrentApp()->getHttp();
        $pub_numbers = $http->post('pub-numbers');
        $search_query = implode(', ', $pub_numbers ?? []);

        $tender_search = new TenderSearch(TenderSearchMode::targeted->value);
        $tender_search->fetchTendersFromApi($search_query);

        if (!empty($tender_search->getLastError())) {
            if ('json' === $http->post('format')) {
                return $this->sendJson([
                    'error' => $tender_search->getLastError(),
                ]);
            } else {
                return $this->showView('error', [
                    'error' => $tender_search->getLastError()
                ], [], 400);
            }
        }

        $saving_status = $tender_search->getTenderList()->saveList();

        if ('json' === $http->post('format')) {
            return $this->sendJson([
                'saving_status' => $saving_status,
            ]);
        } else {
            return $this->toUrl($http->getReferer());
        }
    }

    public function savedTenders()
    {
        $saved_tenders = new TenderList(Tender::findAll());

        return $this->showView('saved-tenders', [
            'saved_tenders' => $saved_tenders,
        ]);
    }

    public function deleteTenders()
    {
        $http = Core::getCurrentApp()->getHttp();
        $pub_numbers = $http->post('pub-numbers');

        if (!is_array($pub_numbers)) {
            return $this->showView('error', [
                'error' => 'array of publication numbers was expected'
            ], [], 400);
        }

        $deleting_status = [];
        foreach ($pub_numbers as $pub_number) {
            if (Tender::deleteOneBy('publication_number', $pub_number)) {
                $deleting_status[] = 'deleted';
            } else {
                $deleting_status[] = 'failed';
            }
        }
        if ('json' === $http->post('format')) {
            return $this->sendJson([
                'deleting_status' => $deleting_status,
            ]);
        } else {
            return $this->toUrl($http->getReferer());
        }
    }
}
