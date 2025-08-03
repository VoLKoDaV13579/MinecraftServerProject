<?php

namespace App\Services;

use App\Repositories\AuthMeUserRepository;
use Carbon\Carbon;

class AuthMeStatsService
{
    public function __construct(
        private AuthMeUserRepository $userRepository
    )
    {
    }

    /**
     * Получить общую статистику
     */
    public function getGeneralStats(): array
    {
        return [
            'total_users' => $this->userRepository->getTotalUsers(),
            'online_users' => $this->userRepository->getOnlineUsersCount(),
            'registered_today' => $this->userRepository->getRegisteredToday(),
            'registered_this_week' => $this->userRepository->getRegisteredThisWeek(),
            'registered_this_month' => $this->userRepository->getRegisteredThisMonth(),
        ];
    }

    /**
     * Получить список онлайн игроков
     */
    public function getOnlineUsers(): array
    {
        return $this->userRepository->getOnlineUsers()->map(function ($user) {
            return [
                'username' => $user->username,
                'realname' => $user->realname,
                'world' => $user->world,
                'coordinates' => $user->getCoordinates(),
                'last_login' => $user->getFormattedLastLogin()
            ];
        })->toArray();
    }

    /**
     * Получить статистику регистраций по дням
     */
    public function getRegistrationStats(int $days = 30): array
    {
        $stats = [];
        $startDate = Carbon::now()->subDays($days);

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $count = $this->userRepository->getRegistrationsForDate($date);

            $stats[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }

        return $stats;
    }

    /**
     * Получить топ игроков по времени игры
     */
    public function getTopPlayers(int $limit = 10): array
    {
        return $this->userRepository->getTopPlayersByPlaytime($limit);
    }

    /**
     * Получить статистику по мирам
     */
    public function getWorldStats(): array
    {
        return $this->userRepository->getWorldStats();
    }

    /**
     * Получить статистику активности
     */
    public function getActivityStats(): array
    {
        $totalUsers = $this->userRepository->getTotalUsers();
        $onlineUsers = $this->userRepository->getOnlineUsersCount();
        $activeToday = $this->userRepository->getActiveToday();
        $activeThisWeek = $this->userRepository->getActiveThisWeek();

        return [
            'total_users' => $totalUsers,
            'online_users' => $onlineUsers,
            'online_percentage' => $totalUsers > 0 ? round(($onlineUsers / $totalUsers) * 100, 2) : 0,
            'active_today' => $activeToday,
            'active_this_week' => $activeThisWeek,
            'activity_rate_today' => $totalUsers > 0 ? round(($activeToday / $totalUsers) * 100, 2) : 0,
            'activity_rate_week' => $totalUsers > 0 ? round(($activeThisWeek / $totalUsers) * 100, 2) : 0,
        ];
    }
}

