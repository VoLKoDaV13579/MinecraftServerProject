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
        $this->assertEquals(68, strlen($hash));
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
    }

    public function test_hash_validation(): void
    {
        $validHash = '$SHA$' . str_repeat('a', 64);
        $invalidHash = 'invalid_hash';

        $this->assertTrue($this->passwordService->isValidHash($validHash));
        $this->assertFalse($this->passwordService->isValidHash($invalidHash));
    }

    public function test_random_password_generation(): void
    {
        $password = $this->passwordService->generateRandomPassword(12);

        $this->assertEquals(12, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9!@#$%^&*]+$/', $password);
    }
}
