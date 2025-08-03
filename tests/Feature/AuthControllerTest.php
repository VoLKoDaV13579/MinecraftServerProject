<?php

namespace Tests\Feature;

use App\Models\AuthMeUser;
use App\Services\AuthMePasswordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AuthMePasswordService $passwordService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordService = app(AuthMePasswordService::class);
    }

    public function test_user_registration(): void
    {
        $userData = [
            'username' => 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'email' => 'test@example.com',
            'realname' => 'Test User'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'user' => [
                    'username',
                    'realname',
                    'email',
                    'registered_at'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('authme', [
            'username' => 'testuser',
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_login(): void
    {
        $user = AuthMeUser::factory()->withPassword('password123')->create([
            'username' => 'testuser'
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'user',
                'token',
                'token_type',
                'message'
            ]);
    }

    public function test_invalid_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'nonexistent',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_get_user_profile(): void
    {
        $user = AuthMeUser::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'user' => [
                    'username',
                    'realname',
                    'world',
                    'coordinates'
                ]
            ]);
    }

    public function test_update_profile(): void
    {
        $user = AuthMeUser::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/profile', [
                'realname' => 'New Name',
                'email' => 'new@example.com'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Профиль успешно обновлен'
            ]);
    }

    public function test_change_password(): void
    {
        $user = AuthMeUser::factory()->withPassword('oldpassword')->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    public function test_logout(): void
    {
        $user = AuthMeUser::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Успешный выход из системы'
            ]);
    }
}
