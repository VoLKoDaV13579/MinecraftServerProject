<?php

namespace Tests\Feature;

use App\Models\AuthMeUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_general_stats(): void
    {
        AuthMeUser::factory(10)->create();
        AuthMeUser::factory(3)->online()->create();

        $response = $this->getJson('/api/v1/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'server' => [
                    'total_users',
                    'online_users',
                    'registered_today',
                    'registered_this_week',
                    'registered_this_month'
                ]
            ]);
    }

    public function test_get_online_users(): void
    {
        AuthMeUser::factory(3)->online()->create();
        AuthMeUser::factory(2)->offline()->create();

        $response = $this->getJson('/api/v1/stats/online');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'online_users',
                'count'
            ])
            ->assertJson([
                'count' => 3
            ]);
    }
}
