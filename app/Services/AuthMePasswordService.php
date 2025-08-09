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
        $hash = hash('sha256', $password . $salt); // Исправлено: убрано двойное хеширование пароля
        return '$SHA$' . $hash;
    }

    /**
     * Проверяет пароль против хеша AuthMe
     */
    public function verify(string $password, string $hashedPassword, string $username): bool
    {
        return hash_equals($this->hash($password, $username), $hashedPassword); // Защита от timing attacks
    }

    /**
     * Проверяет формат хеша AuthMe
     */
    public function isValidHash(string $hash): bool
    {
        if (!str_starts_with($hash, '$SHA$') || strlen($hash) !== 69) {
            return false;
        }

        // Проверяем, что часть после '$SHA$' содержит только hex-символы
        $hashPart = substr($hash, 5);
        return ctype_xdigit($hashPart);
    }

    /**
     * Генерирует случайный пароль
     */
    public function generateRandomPassword(int $length = 12): string
    {
        if ($length < 4) {
            throw new \InvalidArgumentException('Длина пароля должна быть не менее 4 символов');
        }

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }
}
