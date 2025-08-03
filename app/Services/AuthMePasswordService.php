<?php

namespace App\Services;

class AuthMePasswordService
{
    /**
     * Хеширует пароль в формате AuthMe SHA256
     */
    public function hash(string $password, string $username): string
    {
        $salt = hash('sha256', strtolower($username));
        $hash = hash('sha256', hash('sha256', $password) . $salt);

        return '$SHA$' . $hash;
    }

    /**
     * Проверяет пароль против хеша AuthMe
     */
    public function verify(string $password, string $hashedPassword, string $username): bool
    {
        return $this->hash($password, $username) === $hashedPassword;
    }

    /**
     * Проверяет формат хеша AuthMe
     */
    public function isValidHash(string $hash): bool
    {
        return str_starts_with($hash, '$SHA$') && strlen($hash) === 68;
    }

    /**
     * Генерирует случайный пароль
     */
    public function generateRandomPassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
