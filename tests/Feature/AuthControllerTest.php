<?php

namespace Tests\Feature;

use App\Models\AuthMeUser;
use App\Services\AuthMePasswordService;
use Database\Seeders\AuthMeUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $seed = true;

    private AuthMePasswordService $passwordService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordService = app(AuthMePasswordService::class);
        //$this->seed(AuthMeUserSeeder::class);
    }

    public function test_user_registration(): void
    {
        $userData = [
            'username' => 'unittestuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'email' => 'unittest@example.com',
            'realname' => 'Test User'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'user' => [
                    'username',
                    'realname',
                    'registered_at'
                ],
                'message'
            ]);
        if ($response->json('user.email')) {
            $response->assertJsonPath('user.email', $userData['email']);
        }
        $this->assertDatabaseHas('authme', [
            'username' => 'unittestuser',
            'email' => 'unittest@example.com'
        ]);
    }

    public function test_user_login(): void
    {
        $user = AuthMeUser::factory()->create([
            'username' => 'unittestuser'
        ]);

        $passwordService = app(AuthMePasswordService::class);
        $user->password = $passwordService->hash('password123', 'unittestuser');
        $user->save();

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'unittestuser',
            'password' => 'password123'
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'user' => [  // Убрали 'data' =>
                    'id',
                    'username',
                    'realname',
                    'world',
                    'coordinates' => [
                        'x',
                        'y',
                        'z',
                        'world',
                        'yaw',
                        'pitch'
                    ],
                    'is_online',
                    'registered_at',
                    'last_login_at'
                ],
                'token',
                'token_type',
                'expires_in',
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
                'data' => [
                    'user' => [
                        'id',
                        'username',
                        'realname',
                        'email',
                        'world',
                        'coordinates' => [
                            'x',
                            'y',
                            'z',
                            'world',
                            'yaw',
                            'pitch'
                        ],
                        'is_online',
                        'registered_at',
                        'last_login_at',
                        'registration_ip',
                        'last_ip'
                    ]
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
