<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Models\DbModel;

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

    public function getFields(bool $without_id = true): array
    {
        $fields = [];
        foreach (static::$db_columns as $column_name) {
            if ($without_id && 'id' === $column_name) {
                continue;
            }

            $fields[$column_name] = $this->{$column_name};
        }

        return $fields;
    }

    public static function getStub(): self
    {
        $tender = new self();

        foreach (static::$db_columns as $column_name) {
            if ('id' === $column_name) {
                continue;
            }

            $tender->{$column_name} = '';
        }

        $tender->publication_number = '000000-0000';

        return $tender;
    }
}
