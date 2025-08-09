<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Публичные маршруты аутентификации
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->name('api.auth.register');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('api.auth.login');
    });

    // Публичные маршруты статистики
    Route::prefix('stats')->group(function () {
        Route::get('/', [StatsController::class, 'index'])->name('api.stats.index');
        Route::get('/online', [StatsController::class, 'onlineUsers'])->name('api.stats.online');
        Route::get('/registrations', [StatsController::class, 'registrationStats'])->name('api.stats.registrations');
        Route::get('/top-players', [StatsController::class, 'topPlayers'])->name('api.stats.top-players');
        Route::get('/activity', [StatsController::class, 'activityStats'])->name('api.stats.activity');
        Route::get('/worlds', [StatsController::class, 'worldStats'])->name('api.stats.worlds');
    });

    // Публичные маршруты пользователей
    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('api.users.index');
        Route::get('/search', [UsersController::class, 'search'])->name('api.users.search');
        Route::get('/latest', [UsersController::class, 'latest'])->name('api.users.latest');
        Route::get('/worlds', [UsersController::class, 'worlds'])->name('api.users.worlds');
        Route::get('/world/{world}', [UsersController::class, 'byWorld'])->name('api.users.by-world');
        Route::get('/{username}', [UsersController::class, 'show'])->name('api.users.show');
    });

    // Защищенные маршруты
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.auth.update-profile');
            Route::put('/password', [AuthController::class, 'changePassword'])->name('api.auth.change-password');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
            Route::get('/check-token', [AuthController::class, 'checkToken'])->name('api.auth.check-token');
        });
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API работает',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0')
    ]);
})->name('api.health');
