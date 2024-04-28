<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Core;
use DiplomaProject\Core\Interfaces\DataBaseModelInterface;
use DiplomaProject\Core\Interfaces\UserInterface;

class User implements UserInterface, DataBaseModelInterface
{
    public static string $db_table_name = 'users';
    private array $errors = [];

    /**
     * if the value is set to "true", then INSERT will be used to save the User
     */
    private bool $is_saved_in_db = false;

    public const STATUS_GUEST = 0;
    public const STATUS_ADMIN = 5;

    public int $id;
    public string $login;
    public string $password;
    public string $token;
    public int $status;

    public static function getUserByLogin(string $login): ?self
    {
        return static::getOneBy('login', $login);
    }

    public static function getUserByToken(string $token): ?self
    {
        return static::getOneBy('token', $token);
    }

    public static function getGuest(): self
    {
        $guest = new User();
        $guest->status = self::STATUS_GUEST;
        return $guest;
    }

    public function isAdmin(): bool
    {
        return (self::STATUS_ADMIN === $this->status);
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getLogin(): string
    {
        return $this->login;
    }
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $this->generatePasswordHash($password);
    }

    public function generatePasswordHash(string $password): string
    {
        return Core::getCurrentApp()->getSecurity()->generatePasswordHash($password);
    }

    public function validatePassword(string $password): bool
    {
        return ($this->generatePasswordHash($password) === $this->password);
    }

    /**
     * @param integer $lifetime token lifetime in minuts
     */
    public function generateToken(int $lifetime = 0): string
    {
        $expiration_time = time() + $lifetime * 60;
        return Core::getCurrentApp()->getSecurity()->generateToken($this->login, $expiration_time);
    }

    public function validateToken(): bool
    {
        if (empty($this->token)) {
            return false;
        }

        return Core::getCurrentApp()->getSecurity()->validateToken($this->token, $this->login);
    }

    public static function getOneBy(string $field_name, string $value, string $type = 'string'): ?self
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = self::$db_table_name;

        $query_string = "SELECT * FROM `{$table_name}` WHERE `{$field_name}`=? LIMIT 1;";
        $db->execQuery($query_string, [['type' => $type, 'value' => $value]]);

        $result_array = $db->getResultAsArray();

        if (count($result_array) === 0) {
            return null;
        }

        return self::getUserFromArray($result_array[0], true);
    }

    /**
     * Creates a new User object and fills its properties with data from $fields;
     * Does not validate the input data
     */
    private static function getUserFromArray(array $fields, bool $is_saved_in_db = false): self
    {
        $user = new User();

        foreach ($fields as $key => $value) {
            if (!property_exists($user, $key)) {
                continue;
            }

            $user->{$key} = $value;
        }

        if ($is_saved_in_db) {
            $user->setSavedInDb();
        }

        return $user;
    }

    public static function getAll(): array
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = self::$db_table_name;

        $query_string = "SELECT * FROM {$table_name} ORDER BY `id` DESC";
        $db->execQuery($query_string);

        $result_array = $db->getResultAsArray();

        return $result_array;
    }

    private function insert(): bool
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = self::$db_table_name;

        $query_string =
            "INSERT INTO `{$table_name}`(`login`, `password`, `token`, `status`)
            VALUES (?,?,?,?);";
        $db->execQuery($query_string, [
            ['type' => 'string', 'value' => $this->login],
            ['type' => 'string', 'value' => $this->password],
            ['type' => 'string', 'value' => $this->token],
            ['type' => 'int',    'value' => $this->status],
        ]);

        $insert_id = $db->getInsertId();

        if (!$insert_id) {
            return false;
        }

        $this->id = $insert_id;
        return true;
    }

    private function update(): bool
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = self::$db_table_name;

        $query_string =
            "UPDATE `{$table_name}`
            SET `login` = ?,
                `password` = ?,
                `token` = ?,
                `status` = ?
            WHERE `id` = ?;";
        $db->execQuery($query_string, [
            ['type' => 'string', 'value' => $this->login],
            ['type' => 'string', 'value' => $this->password],
            ['type' => 'string', 'value' => $this->token],
            ['type' => 'int',    'value' => $this->status],
            ['type' => 'int',    'value' => $this->id],
        ]);

        return ($db->countAffectedRows() > 0);
    }

    public function save(): bool
    {
        $result = false;

        if (!$this->is_saved_in_db) {
            $result = $this->insert();
        } else {
            $result = $this->update();
        }

        if (!$result) {
            return false;
        }

        $this->is_saved_in_db = true;
        return true;
    }

    public function isSavedInDb(): bool
    {
        return $this->is_saved_in_db;
    }

    private function setSavedInDb()
    {
        $this->is_saved_in_db = true;
    }

    public function validate(): bool
    {
        $this->errors = [];

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
