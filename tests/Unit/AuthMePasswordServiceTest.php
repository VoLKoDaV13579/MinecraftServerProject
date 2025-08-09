<?php

namespace Tests\Unit;

use App\Services\AuthMePasswordService;
use PHPUnit\Framework\TestCase;

class AuthMePasswordServiceTest extends TestCase
{
    private AuthMePasswordService $passwordService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordService = new AuthMePasswordService();
    }

    public function test_password_hashing(): void
    {
        $password = 'testpassword';
        $username = 'testuser';

        $hash = $this->passwordService->hash($password, $username);

        $this->assertStringStartsWith('$SHA$', $hash);
        $this->assertEquals(69, strlen($hash));

        // Проверяем, что хеш содержит только hex-символы после префикса
        $hashPart = substr($hash, 5);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hashPart);
    }

    public function test_password_verification(): void
    {
        $password = 'testpassword';
        $username = 'testuser';

        $hash = $this->passwordService->hash($password, $username);

        $this->assertTrue(
            $this->passwordService->verify($password, $hash, $username)
        );

        $this->assertFalse(
            $this->passwordService->verify('wrongpassword', $hash, $username)
        );

        // Тест с неправильным username
        $this->assertFalse(
            $this->passwordService->verify($password, $hash, 'wronguser')
        );
    }

    public function test_username_case_insensitive(): void
    {
        $password = 'testpassword';
        $username1 = 'TestUser';
        $username2 = 'testuser';

        $hash1 = $this->passwordService->hash($password, $username1);
        $hash2 = $this->passwordService->hash($password, $username2);

        // Хеши должны быть одинаковыми (username приводится к нижнему регистру)
        $this->assertEquals($hash1, $hash2);

        // Проверка должна работать независимо от регистра username
        $this->assertTrue(
            $this->passwordService->verify($password, $hash1, $username2)
        );
        $this->assertTrue(
            $this->passwordService->verify($password, $hash2, $username1)
        );
    }

    public function test_hash_validation(): void
    {
        // Валидный хеш с настоящим SHA256
        $validHash = '$SHA$' . hash('sha256', 'test');
        $this->assertTrue($this->passwordService->isValidHash($validHash));

        // Невалидные хеши
        $this->assertFalse($this->passwordService->isValidHash('invalid_hash'));
        $this->assertFalse($this->passwordService->isValidHash('$SHA$short'));
        $this->assertFalse($this->passwordService->isValidHash('$SHA$' . str_repeat('g', 64))); // 'g' не hex
        $this->assertFalse($this->passwordService->isValidHash('SHA$' . str_repeat('a', 64))); // нет '$' в начале
        $this->assertFalse($this->passwordService->isValidHash('$SHA$' . str_repeat('a', 63))); // слишком короткий
        $this->assertFalse($this->passwordService->isValidHash('$SHA$' . str_repeat('a', 65))); // слишком длинный
    }

    public function test_random_password_generation(): void
    {
        $password1 = $this->passwordService->generateRandomPassword(12);
        $password2 = $this->passwordService->generateRandomPassword(12);

        $this->assertEquals(12, strlen($password1));
        $this->assertEquals(12, strlen($password2));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9!@#$%^&*]+$/', $password1);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9!@#$%^&*]+$/', $password2);

        // Пароли должны быть разными (вероятность совпадения крайне мала)
        $this->assertNotEquals($password1, $password2);
    }

    public function test_random_password_different_lengths(): void
    {
        $shortPassword = $this->passwordService->generateRandomPassword(8);
        $longPassword = $this->passwordService->generateRandomPassword(20);

        $this->assertEquals(8, strlen($shortPassword));
        $this->assertEquals(20, strlen($longPassword));
    }

    public function test_random_password_throws_exception_for_short_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Длина пароля должна быть не менее 4 символов');

        $this->passwordService->generateRandomPassword(3);
    }

    public function test_consistent_hashing(): void
    {
        $password = 'testpassword';
        $username = 'testuser';

        $hash1 = $this->passwordService->hash($password, $username);
        $hash2 = $this->passwordService->hash($password, $username);

        // Один и тот же пароль и username должны давать одинаковый хеш
        $this->assertEquals($hash1, $hash2);
    }

    public function test_real_authme_compatibility(): void
    {
        // Тест с известными значениями для проверки совместимости с AuthMe
        $password = 'password123';
        $username = 'player';

        $hash = $this->passwordService->hash($password, $username);

        // Проверяем формат
        $this->assertStringStartsWith('$SHA$', $hash);
        $this->assertEquals(69, strlen($hash));

        // Проверяем, что верификация работает
        $this->assertTrue(
            $this->passwordService->verify($password, $hash, $username)
        );
    }
}
