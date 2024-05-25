<?php

namespace DiplomaProject\Models;

use DiplomaProject\Core\Core;

class LoginForm extends ValidationForm
{
    public function __construct(
        public string $login,
        public string $password
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

        $authentication = Core::getCurrentApp()->getAuthentication();
        $user = $authentication->getUserByLogin($this->login);

        if (null === $user) {
            $this->addError("user '{$this->login}' not found");
            return false;
        } elseif (!$user->validatePassword($this->password)) {
            $this->addError('invalid login or password');
            return false;
        }

        if (!$user->isEnabled()) {
            $this->addError('your account is not activated or disabled');
        }

        return $this->hasErrors();
    }
}
