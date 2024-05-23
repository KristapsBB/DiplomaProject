<?php

namespace DiplomaProject\Models;

use DiplomaProject\Enums\TenderSearchMode;
use DiplomaProject\Exceptions\TendersApiException;

class TenderSearch
{
    private $search_query = '';

    /**
     * @var Tender[] $tenders
     */
    private array $tenders = [];
    private int $total_tenders_count = 0;
    private int $tenders_per_page = 10;
    private string $last_error = '';
    private string $search_mode = TenderSearchMode::simple->value;

    public function __construct(string $search_mode = '')
    {
        $this->setMode($search_mode);
    }

    public function isResultsEmpty(): bool
    {
        return ($this->total_tenders_count <= 0);
    }

    public function getSearchQuery(): string
    {
        return $this->search_query;
    }

    public function setMode(string $mode)
    {
        if (!empty($mode)) {
            $this->search_mode = $mode;
        }
    }

    public function fetchTendersFromApi(string $search_query, int $page = 1): bool
    {
        $this->search_query = $search_query;

        $this->last_error = '';
        $this->tenders = [];
        $this->total_tenders_count = 0;

        try {
            $tenders_api = new TendersAPI();
            $tenders_api->setQueryText($search_query);
            $tenders_api->setTendersPerPage($this->tenders_per_page);
            $tenders_api->setFields([
                // 'publication-number', // API returns by default
                'notice-title',
                'publication-date',
                'place-of-performance',
                'buyer-name',
                'contract-nature',
                'publication-date',
                'deadline-receipt-tender-date-lot',
            ]);
            $tenders_api->setMode($this->search_mode);

            $tenders_data = $tenders_api->fetchTendersData($page);

            $this->total_tenders_count = $tenders_data['total_tenders_count'];
            unset($tenders_data['total_tenders_count']);

            $formatted_tenders_data = TendersAPI::formatTendersData($tenders_data['tenders']);
        } catch (TendersApiException $e) {
            $this->last_error = $e->getMessage();
            return false;
        }

        foreach ($formatted_tenders_data as $tender_fields) {
            $this->tenders[] = self::getTenderFromArray($tender_fields);
        }

        return true;
    }

    public function countTenders(): int
    {
        return $this->total_tenders_count;
    }

    public function countPages(): int
    {
        $number_tenders = $this->total_tenders_count;

        if ($this->total_tenders_count > TendersAPI::MAX_TENDERS_PER_REQUEST) {
            $number_tenders = TendersAPI::MAX_TENDERS_PER_REQUEST;
        }

        return ceil($number_tenders / $this->tenders_per_page);
    }

    public function getTenderList(): TenderList
    {
        return (new TenderList($this->tenders));
    }

    public function getLastError(): string
    {
        return $this->last_error;
    }

    private static function getTenderFromArray(array $raw_fields): Tender
    {
        $allowed = [
            'publication-number'   => 'publication_number',
            'publication-date'     => 'publication_date',
            'notice-title'         => 'notice_title',
            'place-of-performance' => 'country',
            'buyer-name'           => 'buyer_name',
            'contract-nature'      => 'contract_nature',
            'link'                 => 'link',
            'deadline-receipt-tender-date-lot' => 'deadline',
        ];

        $new_tender = new Tender();

        foreach ($allowed as $field_name => $property_name) {
            if (!array_key_exists($field_name, $raw_fields)) {
                continue;
            }

            $new_tender->{$property_name} = $raw_fields[$field_name];
        }

        return $new_tender;
    }

    public function getModes(): array
    {
        $modes = [
            'by full text'   => [
                'value'   => TenderSearchMode::simple->value,
            ],
            'by publications numbers' => [
                'value' => TenderSearchMode::targeted->value,
            ],
        ];

        foreach ($modes as $key => $mode) {
            if ($this->search_mode === $mode['value']) {
                $modes[$key]['selected'] = true;
            } else {
                $modes[$key]['selected'] = false;
            }
        }

        return $modes;
    }
}
