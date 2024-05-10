<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\DbModel;

class Tender extends DbModel
{
    public static string $db_table_name = 'tenders';
    public static array $db_columns = [
        'id',
        'publication_number',
        'publication_date',
        'notice_title',
        'buyer_name',
        'country',
        'contract_nature',
        'deadline',
        'link'
    ];

    public int $id;
    public string $publication_number;
    public string $publication_date;
    public string $notice_title;
    public string $buyer_name;
    public string $country;
    public ?string $contract_nature = null;
    public ?string $deadline = null;
    public string $link;

    public function getFields(): array
    {
        return [
            'publication_number' => $this->publication_number,
            'notice_title' => $this->notice_title,
            'country' => $this->country,
            'buyer_name' => $this->buyer_name,
            'contract_nature' => $this->contract_nature ?? '',
            'publication_date' => $this->publication_date,
            'deadline' => $this->deadline ?? '',
            'link' => $this->link,
        ];
    }

    public static function getStub(): self
    {
        $tender = new self();
        $tender->publication_number = '000000-0000';
        $tender->notice_title = '';
        $tender->country = '';
        $tender->buyer_name = '';
        $tender->contract_nature = '';
        $tender->publication_date = '';
        $tender->deadline = '';
        $tender->link = '';
        return $tender;
    }
}
