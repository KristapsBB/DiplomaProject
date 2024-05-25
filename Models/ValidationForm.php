<?php

namespace DiplomaProject\Models;

class ValidationForm
{
    private array $errors = [];

    protected function addError(string $message)
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

    protected function resetErrors()
    {
        $this->errors = [];
    }

    protected function hasErrors(): bool
    {
        return (count($this->errors) === 0);
    }
}
