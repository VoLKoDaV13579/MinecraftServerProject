<?php

namespace Database\Factories;

use App\Models\AuthMeUser;
use App\Services\AuthMePasswordService;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthMeUserFactory extends Factory
{
    protected $model = AuthMeUser::class;

    public function definition(): array
    {
        $username = fake()->unique()->userName();
        $passwordService = app(AuthMePasswordService::class);

        return [
            'username' => strtolower($username),
            'realname' => fake()->name(),
            'password' => $passwordService->hash('password', $username),
            'email' => fake()->unique()->safeEmail(),
            'regdate' => fake()->dateTimeBetween('-1 year')->getTimestamp() * 1000,
            'regip' => fake()->ipv4(),
            'ip' => fake()->ipv4(),
            'lastlogin' => fake()->dateTimeBetween('-1 month')->getTimestamp() * 1000,
            'x' => fake()->randomFloat(2, -1000, 1000),
            'y' => fake()->randomFloat(2, 0, 256),
            'z' => fake()->randomFloat(2, -1000, 1000),
            'world' => fake()->randomElement(['world', 'nether', 'end']),
            'yaw' => fake()->randomFloat(2, 0, 360),
            'pitch' => fake()->randomFloat(2, -90, 90),
            'isLogged' => fake()->boolean(20)
        ];
    }

    public function online(): static
    {
        return $this->state(fn() => ['isLogged' => true]);
    }

    public function offline(): static
    {
        return $this->state(fn() => ['isLogged' => false]);
    }

    public function withPassword(string $password): static
    {
        return $this->state(function (array $attributes) use ($password) {
            $passwordService = app(AuthMePasswordService::class);
            return [
                'password' => $passwordService->hash($password, $attributes['username'])
            ];
        });
    }
}
