<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'api_version' => 'v1',
        'docs' => url('/api/v1/health'),
        'endpoints' => [
            'health' => url('/api/health'),
            'stats' => url('/api/v1/stats'),
            'auth' => url('/api/v1/auth/login'),
            'users' => url('/api/v1/users'),
        ]
    ]);
});

// API документация (простая версия)
Route::get('/docs', function () {
    return view('api-docs');
})->name('api.docs');
