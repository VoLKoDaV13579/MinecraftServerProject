<?php

namespace Database\Seeders;

use App\Models\AuthMeUser;
use Illuminate\Database\Seeder;

class AuthMeUserSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем тестового администратора
        AuthMeUser::factory()->create([
            'username' => 'admin',
            'realname' => 'Administrator',
            'email' => 'admin@example.com',
            'isLogged' => true
        ]);

        // Создаем тестового пользователя
        AuthMeUser::factory()->withPassword('password123')->create([
            'username' => 'testuser',
            'realname' => 'Test User',
            'email' => 'test@example.com',
            'isLogged' => false
        ]);

        // Создаем случайных пользователей
        AuthMeUser::factory(500)->create();

        // Создаем онлайн пользователей
        AuthMeUser::factory(100)->online()->create();
    }
}
