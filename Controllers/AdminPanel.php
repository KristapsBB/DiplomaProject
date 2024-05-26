<?php

namespace DiplomaProject\Controllers;

use DiplomaProject\Core\Controller;
use DiplomaProject\Core\Core;
use DiplomaProject\Core\Libs\FileHelper;
use DiplomaProject\Enums\TenderSearchMode;
use DiplomaProject\Models\Pagination;
use DiplomaProject\Models\Tender;
use DiplomaProject\Models\TenderExporter;
use DiplomaProject\Models\TenderList;
use DiplomaProject\Models\TenderSearch;
use DiplomaProject\Models\TenderToUser;
use DiplomaProject\Models\User;

class AdminPanel extends Controller
{
    public function before(string $method_name)
    {
        /**
         * names of methods that can only be accessed by a POST request
         */
        $onlyPost = ['searchAndSave', 'deleteTenders', 'downloadTenderTable'];

        if (!$this->getCurrentUser()->can('find_tenders')) {
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

        $search_query = trim($http->get('search-query') ?? '');
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
            'tender_list' => $tender_search->getTenderList($this->getCurrentUser()->getId()),
            'pagination' => $pagination,
        ]);
    }

    public function getTenderTemplate()
    {
        $tender_fields = Tender::getStub()->getFields();

        foreach (array_keys($tender_fields) as $field_name) {
            $tender_fields[$field_name] = '%' . strtoupper($field_name) . '%';
        }

        Core::getCurrentApp()->getViewer()->changeLayout('empty-layout');

        return $this->showView('tender-list-item', [
            'tender' => $tender_fields,
            'item_data' => [
                'editing_mode' => 'saving',
                'is_saved_css_class' => '%IS_SAVED_CSS_CLASS%',
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

        $tender_list = $tender_search->getTenderList($this->getCurrentUser()->getId());

        $tenders = [];
        foreach ($tender_list->getTenders() as $key => $tender) {
            $tenders[$key] = $tender->getFields();

            if ($tender_list->isTenderSaved($tender->publication_number)) {
                $tenders[$key]['is_saved_css_class'] = 'tender_is-saved';
            }
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

        $saving_status = $tender_search->getTenderList(
            $this->getCurrentUser()->getId()
        )->saveList();

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
        $user = $this->getSelectedUser('user_id');

        if ($user->getId() === $this->getCurrentUser()->getId()) {
            $title = 'your list of tenders';
            $hide_edit_buttons = false;
        } else {
            $title = 'list of tenders of user "' . $user->getLogin() . '"';
            $hide_edit_buttons = true;
        }

        $saved_tenders = new TenderList($user->getTenders(), $user->getId());

        return $this->showView('saved-tenders', [
            'title' => $title,
            'saved_tenders' => $saved_tenders,
            'hide_edit_buttons' => $hide_edit_buttons,
        ]);
    }

    public function deleteTenders()
    {
        $http = Core::getCurrentApp()->getHttp();
        $pub_numbers = $http->post('pub-numbers') ?? [];

        if (!is_array($pub_numbers)) {
            return $this->showView('error', [
                'error' => 'array of publication numbers was expected'
            ], [], 400);
        }

        $user_id = $this->getCurrentUser()->getId();

        $deleting_status = [];
        foreach ($pub_numbers as $pub_number) {
            $res = TenderToUser::deleteBy([
                ['publication_number', '=', $pub_number],
                ['user_id', '=', $user_id],
            ]);

            if ($res) {
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

    public function downloadTenderTable()
    {
        $http = Core::getCurrentApp()->getHttp();
        $get_all_tenders = (bool) $http->post('get-all-tenders') ?? false;
        $pub_numbers = $http->post('pub-numbers') ?? [];

        if (!is_array($pub_numbers) && (true !== $get_all_tenders)) {
            return $this->showView('error', [
                'error' => 'array of publication numbers was expected'
            ], [], 400);
        }

        if (true !== $get_all_tenders && !empty($pub_numbers)) {
            $condition = [['publication_number', 'IN', $pub_numbers]];
        }

        $user = $this->getSelectedUser('user_id');
        $tenders = $user->getTenders($condition ?? []);

        $root = Core::getCurrentApp()->getAppRoot();
        $files_dir = "{$root}runtime/generated-files";
        $fullpath = "$files_dir/tender-table-{$user->login}.xlsx";

        FileHelper::initDir($files_dir);

        if (!(new TenderExporter())->buildXlsxFile($fullpath, $tenders)) {
            if (!is_array($pub_numbers)) {
                return $this->showView('error', [
                    'error' => 'the file could not be created'
                ], [], 500);
            }
        }

        return $this->sendFile($fullpath);
    }

    private function getSelectedUser(string $var_name): User
    {
        $http = Core::getCurrentApp()->getHttp();

        if ($this->isAdmin() && !empty($http->get($var_name))) {
            $user_id = (int) $http->get($var_name);
            $user = User::findOneBy('id', $user_id);
        }

        if (empty($user)) {
            $user = $this->getCurrentUser();
        }

        return $user;
    }
}
