<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Core;

class LoginForm
{
    private array $errors = [];

    public function __construct(
        public string $login,
        public string $password
    ) {
    }

    public function validateForm(): bool
    {
        $this->errors = [];

        if (empty($this->login)) {
            $this->addError("field 'login' is required");
            return false;
        }

        if (empty($this->password)) {
            $this->addError("field 'password' is required");
            return false;
        }

        $authentication = Core::getCurrentApp()->getAuthentication();
        $user = $authentication->getUserByLogin($this->login);

        if (null === $user) {
            $this->addError("user '{$this->login}' not found");
        } elseif (!$user->validatePassword($this->password)) {
            $this->addError('invalid login or password');
        }

        return (count($this->errors) === 0);
    }

    private function addError(string $message)
    {
        $this->errors[] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getLastError(): ?string
    {
        if (count($this->errors) === 0) {
            return null;
        }

        return end($this->errors);
    }
}
