<?php

namespace App\Providers;

use App\Models\AuthMeUser;
use App\Providers\AuthMeUserProvider;
use App\Repositories\AuthMeUserRepository;
use App\Services\AuthMeAuthService;
use App\Services\AuthMePasswordService;
use App\Services\AuthMeStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthMeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрируем сервисы как синглтоны
        $this->app->singleton(AuthMePasswordService::class);
        $this->app->singleton(AuthMeUserRepository::class);

        $this->app->singleton(AuthMeAuthService::class, function ($app) {
            return new AuthMeAuthService(
                $app->make(AuthMeUserRepository::class),
                $app->make(AuthMePasswordService::class)
            );
        });

        $this->app->singleton(AuthMeStatsService::class, function ($app) {
            return new AuthMeStatsService(
                $app->make(AuthMeUserRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ВАЖНО: Регистрируем кастомный user provider
        Auth::provider('authme', function ($app, array $config) {
            return new AuthMeUserProvider(
                $app->make(AuthMePasswordService::class)
            );
        });
    }
}
