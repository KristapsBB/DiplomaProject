<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Models\DbModel;

class TenderToUser extends DbModel
{
    public static string $db_table_name = 'tenders_of_users';
    public static array $db_columns = [
        'id',
        'publication_number',
        'user_id',
    ];

    public string $publication_number;
    public int $user_id;

    public function getTender(): Tender
    {
        return Tender::findOneBy('publication_number', $this->publication_number);
    }

    public function getUser(): User
    {
        return User::findOneBy('id', $this->user_id);
    }
}
