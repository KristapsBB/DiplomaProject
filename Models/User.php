<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Core;
use DiplomaProject\Core\Models\DbModel;
use DiplomaProject\Core\Interfaces\DataBaseModelInterface;
use DiplomaProject\Core\Interfaces\UserInterface;

class User extends DbModel implements UserInterface, DataBaseModelInterface
{
    public static string $db_table_name = 'users';
    public static array $db_columns = [
        'id',
        'login',
        'password',
        'token',
        'status',
    ];

    private array $errors = [];

    public const STATUS_GUEST = 0;
    public const STATUS_ADMIN = 5;

    public int $id;
    public string $login;
    public string $password;
    public string $token;
    public int $status;

    public static function getUserByLogin(string $login): ?self
    {
        return static::findOneBy('login', $login);
    }

    public static function getUserByToken(string $token): ?self
    {
        return static::findOneBy('token', $token);
    }

    public static function getGuest(): self
    {
        $guest = new self();
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
