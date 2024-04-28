<?php

namespace DiplomaProject\Core\Interfaces;

interface DataBaseModelInterface
{
    public static function getOneBy(string $field_name, string $value, string $type): ?self;
    public static function getAll(): array;

    public function validate(): bool;
    public function getErrors(): array;
    public function save(): bool;
}
