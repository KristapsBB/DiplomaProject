<?php

namespace DiplomaProject\Core\Modules;

class Security
{
    private string $salt = '';

    public function configure(array $params)
    {
        $this->setSalt($params['salt']);
    }

    public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    public function generatePasswordHash(string $password): string
    {
        return hash('sha256', $this->salt . $password);
    }

    public function validatePassword(string $hash, string $password): bool
    {
        return ($this->generatePasswordHash($password) === $hash);
    }

    public function generateToken(string $sourse_string, int $expiration_time = 0): string
    {
        $token = hash('sha256', $this->salt . $sourse_string . $expiration_time);
        $token .= ':' . (date('YmdHis', $expiration_time));

        return $token;
    }

    public function validateToken(string $token, string $sourse_string): bool
    {
        // $token_hash = substr($token, 0, 64);
        $expiration_date = substr($token, 65);

        $Y = substr($expiration_date, 0, 4);
        $m = substr($expiration_date, 4, 2);
        $d = substr($expiration_date, 6, 2);

        $H = substr($expiration_date, 8, 2);
        $i = substr($expiration_date, 10, 2);
        $s = substr($expiration_date, 12, 2);

        $expiration_time = strtotime("{$Y}-{$m}-{$d} {$H}:{$i}:{$s}");

        if ($expiration_time <= time()) {
            return false;
        }

        return ($this->generateToken($sourse_string, $expiration_time) === $token);
    }
}
