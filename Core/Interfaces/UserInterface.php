<?php

namespace DiplomaProject\Core\Interfaces;

interface UserInterface
{
    public static function getUserByLogin(string $login): ?self;
    public static function getUserByToken(string $token): ?self;
    public static function getGuest(): self;

    public function isAdmin(): bool;

    public function getId(): int;
    public function getLogin(): string;
    public function setPassword(string $password);
    public function getPassword(): string;
    public function setToken(string $token);
    public function getToken(): string;

    public function generatePasswordHash(string $password): string;
    public function validatePassword(string $password): bool;
    public function generateToken(int $lifetime = 0): string;
    public function validateToken(): bool;
}
