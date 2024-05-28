<?php

namespace DiplomaProject\Core\Interfaces;

interface UserInterface
{
    public static function getUserByLogin(string $login): ?self;
    public static function getUserByToken(string $token_name, string $value): ?self;
    public static function getGuest(): self;

    public function isAdmin(): bool;
    public function can(string $capability): bool;

    public function getId(): int;
    public function getLogin(): string;
    public function setPassword(string $password);
    public function getPassword(): string;
    public function setToken(string $token_name, ?string $value);
    public function getToken(string $token_name): string;
    public function isEnabled(): bool;

    public function isUnblocked(int $attempts_before_blocking): bool;
    public function unblock();
    public function tryBlock(int $attempts_before_blocking, int $minutes);

    public function generatePasswordHash(string $password): string;
    public function validatePassword(string $password): bool;
    public function generateToken(int $lifetime = 0): string;
    public function validateToken(string $token_name): bool;
}
