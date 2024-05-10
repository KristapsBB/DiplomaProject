<?php

namespace DiplomaProject\Models;

class Tender
{
    public int $id;
    public string $publication_number;
    public string $publication_date;
    public string $notice_title;
    public string $buyer_name;
    public array $counry_codes;
    public ?string $contract_nature = null;
    public ?string $deadline = null;
    public string $link;

    public function getCountry(): string
    {
        return implode(', ', $this->counry_codes);
    }

    public function getFields(): array
    {
        return [
            'publication_number' => $this->publication_number,
            'notice_title' => $this->notice_title,
            'country' => $this->getCountry(),
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
        $tender->counry_codes = [''];
        $tender->buyer_name = '';
        $tender->contract_nature = '';
        $tender->publication_date = '';
        $tender->deadline = '';
        $tender->link = '';
        return $tender;
    }
}
