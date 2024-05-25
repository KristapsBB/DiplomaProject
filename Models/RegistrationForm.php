<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Core;

class RegistrationForm extends ValidationForm
{
    public function __construct(
        public string $email,
        public string $login,
        public string $password,
    ) {
    }

    public function validateForm(): bool
    {
        $this->resetErrors();

        if (empty($this->login)) {
            $this->addError("field 'login' is required");
            return false;
        }

        if (empty($this->password)) {
            $this->addError("field 'password' is required");
            return false;
        }

        if (empty($this->email)) {
            $this->addError("field 'email' is required");
            return false;
        }

        if (!empty(User::find([['login', '=', $this->login]]))) {
            $this->addError("username '{$this->login}' busy");
            return false;
        }

        if (!empty(User::find([['email', '=', $this->email]]))) {
            $this->addError("email '{$this->email}' busy");
            return false;
        }

        return $this->hasErrors();
    }
}
