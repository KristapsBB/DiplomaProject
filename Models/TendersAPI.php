<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Models\CurlRequest;
use DiplomaProject\Enums\TenderSearchMode;
use DiplomaProject\Enums\TenderSearchScope;
use DiplomaProject\Exceptions\TendersApiException;

class TendersAPI
{
    /**
     * @var MAX_TENDERS_PER_REQUEST maximum number of tenders per request
     */
    public const MAX_TENDERS_PER_REQUEST = 10000;

    private string $query_text = '';
    private int $tenders_per_page = 10;

    /**
     * field names that the API will return for the found tenders
     */
    private array $fields = ['FT'];
    private TenderSearchScope $scope = TenderSearchScope::ACTIVE;
    private TenderSearchMode $search_mode = TenderSearchMode::simple;

    public function __construct(
        private $api_url = 'https://api.ted.europa.eu/v3/notices/search'
    ) {
    }

    public function setTendersPerPage(int $tenders_per_page)
    {
        if ($tenders_per_page < 1) {
            throw new TendersApiException(400, 'tenders per page must be greater than zero');
        }

        if ($tenders_per_page > 200) {
            throw new TendersApiException(400, 'tenders per page must not exceed 200');
        }

        $this->tenders_per_page = $tenders_per_page;
    }

    public function setQueryText(string $query_text)
    {
        if ('' === $query_text) {
            throw new TendersApiException(400, 'empty search query');
        }
        $this->query_text = $query_text;
    }

    /**
     * sets field names that the API will return for the found tenders
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function setScope(string $scope)
    {
        $this->scope = TenderSearchScope::from(strtoupper($scope));
    }

    public function setMode(string $mode)
    {
        $this->search_mode = TenderSearchMode::from(strtolower($mode));
    }

    private function buildQuery(string $query_text, int $page = 1): array
    {
        if ($page <= 0) {
            throw new TendersApiException(400, '$page parameter must be greater than zero');
        }

        switch ($this->search_mode) {
            case TenderSearchMode::simple:
                $query_string = "(FT ~ ({$query_text}))";
                break;
            case TenderSearchMode::targeted:
                $query_string = "(publication-number IN ({$query_text}))";
                break;
        }

        $query = [
            'query'  => $query_string,
            'fields' => $this->fields,
            'scope' => $this->scope,
            'page'  => $page,
            'limit' => $this->tenders_per_page,
            'checkQuerySyntax' => false,
            'onlyLatestVersions' => false,
        ];

        return $query;
    }

    public function fetchTendersData(int $page = 1): array
    {
        $query = $this->buildQuery($this->query_text, $page);
        $request_body = json_encode($query, JSON_HEX_QUOT);

        $curl_request = new CurlRequest();
        $curl_request->setHeader('accept', '*/*');
        $curl_request->setHeader('Content-Type', 'application/json');
        $curl_request->setBody($request_body);
        $curl_request->send($this->api_url);
        $response = $curl_request->getResponse();

        if (200 !== $response->httpCode()) {
            throw new TendersApiException(
                $response->httpCode(),
                $this->getErrorMessage($response->httpCode())
            );
        }

        $json = $response->json();

        if (!array_key_exists('notices', $json)) {
            throw new TendersApiException($response->httpCode(), 'incorrect API response');
        }

        $result['tenders'] = $json['notices'];

        if (array_key_exists('totalNoticeCount', $json)) {
            $result['total_tenders_count'] = $json['totalNoticeCount'];
        }

        return $result;
    }

    public static function formatTendersData(array $tenders_data): array
    {
        foreach ($tenders_data as $key => $tender_fields) {
            foreach ($tender_fields as $field_name => $tender_field) {
                switch ($field_name) {
                    case 'notice-title':
                        $tenders_data[$key][$field_name] = $tender_field['eng'] ?? end($tender_field);
                        break;
                    case 'buyer-name':
                        $buyer_names = end($tender_field);
                        $tenders_data[$key][$field_name] = end($buyer_names);
                        break;
                    case 'contract-nature':
                        $tenders_data[$key][$field_name] = end($tender_field);
                        break;
                    case 'deadline-receipt-tender-date-lot':
                        $tenders_data[$key][$field_name] = substr(end($tender_field), 0, 10);
                        break;
                    case 'place-of-performance':
                        $tenders_data[$key][$field_name] = implode(', ', $tender_field);
                        break;
                    case 'publication-date':
                        $tenders_data[$key][$field_name] = substr($tender_field, 0, 10);
                        break;
                }
            }

            $tenders_data[$key]['link'] = $tenders_data[$key]['links']['html']['ENG'] ?? '';
            unset($tenders_data[$key]['links']);
        }

        return $tenders_data;
    }

    private function getErrorMessage(int $http_code): string
    {
        if (429 === $http_code) {
            return 'too many requests, try again later';
        }

        if ($http_code >= 400 && $http_code <= 499) {
            return 'invalid search query, change your query and try again';
        }

        if ($http_code >= 500 && $http_code <= 599) {
            return 'API server error';
        }

        return 'unknown error has occurred';
    }
}
