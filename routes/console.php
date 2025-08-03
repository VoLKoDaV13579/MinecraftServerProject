<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('authme:stats {--json : Output as JSON}', function () {
    $statsService = app(\App\Services\AuthMeStatsService::class);
    $stats = $statsService->getGeneralStats();

    if ($this->option('json')) {
        $this->line(json_encode($stats, JSON_PRETTY_PRINT));
        return;
    }

    $this->table(
        ['Метрика', 'Значение'],
        [
            ['Всего пользователей', $stats['total_users']],
            ['Онлайн', $stats['online_users']],
            ['Зарегистрировано сегодня', $stats['registered_today']],
            ['За неделю', $stats['registered_this_week']],
            ['За месяц', $stats['registered_this_month']],
        ]
    );
})->purpose('Показать статистику AuthMe');

Artisan::command('authme:cleanup-tokens', function () {
    $deleted = \Laravel\Sanctum\PersonalAccessToken::where('expires_at', '<', now())->delete();
    $this->info("Удалено просроченных токенов: {$deleted}");
})->purpose('Очистка просроченных токенов');

Artisan::command('authme:cleanup-inactive {days=30}', function ($days) {
    $repository = app(\App\Repositories\AuthMeUserRepository::class);
    $inactiveUsers = $repository->getInactiveUsers($days);

    $this->table(
        ['Username', 'Last Login', 'Registered'],
        $inactiveUsers->map(function ($user) {
            return [
                $user->username,
                $user->getFormattedLastLogin() ?? 'Никогда',
                $user->getFormattedRegDate()
            ];
        })->toArray()
    );

    $this->info("Найдено неактивных пользователей: {$inactiveUsers->count()}");
})->purpose('Показать неактивных пользователей');

Schedule::command('authme:cleanup-tokens')->daily();
