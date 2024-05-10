<?php

namespace DiplomaProject\Core\Interfaces;

interface DataBaseModelInterface
{
    public static function findOneBy(string $field_name, string $value, string $type): ?static;
    public static function findAll(): array;

    public function validate(): bool;
    public function getErrors(): array;
    public function save(): bool;
}
