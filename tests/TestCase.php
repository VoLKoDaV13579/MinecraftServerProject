<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Настройка для тестов
        config(['database.default' => 'testing']);
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
