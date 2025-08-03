<?php

namespace Tests\Feature;

use App\Models\AuthMeUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_users_list(): void
    {
        AuthMeUser::factory(5)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta',
                'links'
            ]);
    }

    public function test_search_users(): void
    {
        AuthMeUser::factory()->create(['username' => 'testuser']);
        AuthMeUser::factory()->create(['username' => 'anotheruser']);

        $response = $this->getJson('/api/v1/users/search?q=test');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'users',
                'query',
                'count'
            ])
            ->assertJson([
                'query' => 'test',
                'count' => 1
            ]);
    }

    public function test_get_user_profile(): void
    {
        $user = AuthMeUser::factory()->create(['username' => 'testuser']);

        $response = $this->getJson('/api/v1/users/testuser');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'user' => [
                    'username',
                    'realname',
                    'world',
                    'is_online'
                ]
            ]);
    }

    public function test_user_not_found(): void
    {
        $response = $this->getJson('/api/v1/users/nonexistent');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error_code' => 'USER_NOT_FOUND'
            ]);
    }
}
