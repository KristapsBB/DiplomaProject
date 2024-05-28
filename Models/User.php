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
        'email',
        'password',
        'auth_token',
        'access_level',
        'rescue_token',
        'status',
        'number_of_failed_login',
        'datetime_of_unblocking',
    ];

    private array $errors = [];

    public const ACCESS_LEVEL_GUEST  = 0;
    public const ACCESS_LEVEL_FINDER = 2;
    public const ACCESS_LEVEL_ADMIN  = 5;

    public const DEFAULT_ACCESS_LEVEL = self::ACCESS_LEVEL_FINDER;

    public const STATUS_NOT_ACTIVATED = 0;
    public const STATUS_ENABLED = 8;
    public const STATUS_DISABLED = 9;
    public const STATUS_DELETED = 11;

    private static array $statuses = [
        self::STATUS_DELETED => 'deleted',
        self::STATUS_DISABLED => 'disabled',
        self::STATUS_ENABLED => 'enabled',
        self::STATUS_NOT_ACTIVATED => 'not activated',
    ];

    public int $id;
    public string $login;
    public string $email;
    public string $password;
    public ?string $auth_token = null;
    public int $access_level = self::ACCESS_LEVEL_GUEST;
    public ?string $rescue_token = null;
    public int $status = self::STATUS_NOT_ACTIVATED;
    public int $number_of_failed_login = 0;
    public ?string $datetime_of_unblocking = null;

    public static function getUserByLogin(string $login): ?self
    {
        return static::findOneBy('login', $login);
    }

    public static function getUserByToken(string $token_name, string $value): ?self
    {
        return static::findOneBy("{$token_name}_token", $value);
    }

    public static function getGuest(): self
    {
        $guest = new self();
        $guest->login = 'guest';
        $guest->access_level = self::ACCESS_LEVEL_GUEST;
        return $guest;
    }

    public function isAdmin(): bool
    {
        return (self::ACCESS_LEVEL_ADMIN === $this->access_level);
    }

    public function can(string $capability): bool
    {
        if (false !== array_search($capability, ['self-deleting', 'self-disabling'])) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        $rules = [
            'find_tenders'   => static::ACCESS_LEVEL_FINDER,
            'edit_users'     => static::ACCESS_LEVEL_ADMIN,
        ];

        foreach ($rules as $cap_name => $level) {
            if ($cap_name === $capability) {
                return ($this->access_level >= $level);
            }
        }

        return false;
    }

    public function getId(): int
    {
        return $this->id ?? -1;
    }

    public function setToken(string $token_name, ?string $value)
    {
        $this->{$token_name . '_token'} = $value;
    }

    public function getToken(string $token_name): string
    {
        return $this->{$token_name . '_token'};
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
     * @param integer $lifetime token lifetime in minutes
     */
    public function generateToken(int $lifetime = 0): string
    {
        $expiration_time = time() + $lifetime * 60;
        return Core::getCurrentApp()
            ->getSecurity()
            ->generateToken(
                $this->login . ':' . $this->status . ':' . $this->access_level,
                $expiration_time
            );
    }

    public function validateToken(string $token_name): bool
    {
        if (empty($this->{"{$token_name}_token"})) {
            return false;
        }

        return Core::getCurrentApp()
            ->getSecurity()
            ->validateToken(
                $this->{"{$token_name}_token"},
                $this->login . ':' . $this->status . ':' . $this->access_level
            );
    }

    public function validate(): bool
    {
        $this->errors = [];

        if ($this->isSavedInDb() && $this->getId() <= 0) {
            $this->errors[] = 'id is invalid';
            return false;
        }

        if (empty($this->login)) {
            $this->errors[] = 'login is empty';
            return false;
        }

        if (empty($this->email)) {
            $this->errors[] = 'email is empty';
            return false;
        }

        if (strlen($this->login) > 32) {
            $this->errors[] = 'login is too long';
        }

        if (strlen($this->email) > 256) {
            $this->errors[] = 'email is too long';
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'email is invalid';
        }

        if ($user = static::findOneBy('login', $this->login)) {
            if ($user->getId() !== $this->getId()) {
                $this->errors[] = "login '{$this->login}' busy";
            }
        }

        if ($user = static::findOneBy('email', $this->email)) {
            if ($user->getId() !== $this->getId()) {
                $this->errors[] = "login '{$this->email}' busy";
            }
        }

        return !$this->hasErrors();
    }

    public function hasErrors(): bool
    {
        return count($this->errors ?? []);
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function getLastError(): ?string
    {
        $errors = $this->errors ?? [];

        return end($errors);
    }

    /**
     * @return Tender[]
     */
    public function getTenders(array $condition = []): array
    {
        $condition[] = ['user_id', '=', $this->getId()];

        $tenders_to_user = TenderToUser::find($condition);
        $pub_numbers = array_column($tenders_to_user, 'publication_number');

        if (empty($pub_numbers)) {
            return [];
        }

        return Tender::find([['publication_number', 'IN', $pub_numbers]]);
    }

    public static function register(
        string $email,
        string $login,
        string $password,
        int $access_level = self::DEFAULT_ACCESS_LEVEL,
    ): static {
        $new_user = new static();

        $new_user->email = $email;
        $new_user->login = $login;
        $new_user->setPassword($password);
        $new_user->access_level = $access_level;
        $new_user->rescue_token = $new_user->generateRescueToken();
        $new_user->status = self::STATUS_NOT_ACTIVATED;

        if (!$new_user->validate() || !$new_user->save()) {
            return $new_user;
        }

        return $new_user;
    }

    public function generateRescueToken(): string
    {
        return $this->generateToken(24 * 60);
    }

    public function validateRescueToken(): bool
    {
        return $this->validateToken('rescue');
    }

    public static function activate(string $rescue_token): bool
    {
        if (!$user = static::getUserByToken('rescue', $rescue_token)) {
            return false;
        }

        if (self::STATUS_NOT_ACTIVATED !== $user->status) {
            return false;
        }

        if (!$user->validateRescueToken()) {
            return false;
        }

        $user->status = self::STATUS_ENABLED;
        $user->rescue_token = null;
        return $user->save();
    }

    public function getAllStatuses(): array
    {
        return static::$statuses;
    }

    public function getStatusLabel(): string
    {
        if (array_key_exists($this->status, static::$statuses)) {
            return static::$statuses[$this->status];
        }

        return '';
    }

    public function isEnabled(): bool
    {
        return (static::STATUS_ENABLED === $this->status);
    }

    public function incFailedLogin()
    {
        $this->number_of_failed_login += 1;
        $this->save();
    }

    public function countFailedLogin(): int
    {
        return $this->number_of_failed_login;
    }

    public function isUnblocked(int $attempts_before_blocking = 3): bool
    {
        if ($this->number_of_failed_login < $attempts_before_blocking) {
            return true;
        }

        if (empty($this->datetime_of_unblocking)) {
            return false;
        }

        $current = new \DateTime();
        $unblock = \DateTime::createFromFormat('Y-m-d H:i:s', $this->datetime_of_unblocking);

        return ($current > $unblock);
    }

    public function block(int $minutes = 15)
    {
        $blocking_datetime = new \DateTime();
        $blocking_datetime->add(\DateInterval::createFromDateString("+{$minutes} minutes"));

        $this->datetime_of_unblocking = $blocking_datetime->format('Y-m-d H:i:s');
        $this->save();
    }

    public function unblock()
    {
        $this->number_of_failed_login = 0;
        $this->datetime_of_unblocking = null;
        $this->save();
    }

    public function tryBlock(int $attempts_before_blocking, int $minutes)
    {
        $this->incFailedLogin();

        if ($this->countFailedLogin() >= $attempts_before_blocking) {
            $this->block(15);
        }
    }
}
