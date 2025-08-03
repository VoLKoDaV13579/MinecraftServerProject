<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Настройка для тестов
        config(['database.default' => 'testing']);
    }

    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Создать аутентифицированного пользователя
     */
    protected function createAuthenticatedUser(array $attributes = [])
    {
        $user = \App\Models\AuthMeUser::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;

        return [$user, $token];
    }

    /**
     * Выполнить запрос с аутентификацией
     */
    protected function authenticatedJson(string $method, string $uri, array $data = [], array $headers = [])
    {
        [$user, $token] = $this->createAuthenticatedUser();

        return $this->json($method, $uri, $data, array_merge($headers, [
            'Authorization' => 'Bearer ' . $token
        ]));
    }
}
