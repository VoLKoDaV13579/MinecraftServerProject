<?php

namespace App\Repositories;

use App\Models\AuthMeUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthMeUserRepository
{
    /**
     * Найти пользователя по имени
     */
    public function findByUsername(string $username): ?AuthMeUser
    {
        return AuthMeUser::where('username', strtolower($username))->first();
    }

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?AuthMeUser
    {
        return AuthMeUser::where('email', $email)->first();
    }

    /**
     * Найти пользователя по ID
     */
    public function findById(int $id): ?AuthMeUser
    {
        return AuthMeUser::find($id);
    }

    /**
     * Создать нового пользователя
     */
    public function create(array $data): AuthMeUser
    {
        return AuthMeUser::create($data);
    }

    /**
     * Обновить данные пользователя
     */
    public function update(AuthMeUser $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Удалить пользователя
     */
    public function delete(AuthMeUser $user): bool
    {
        return $user->delete();
    }

    /**
     * Получить всех онлайн пользователей
     */
    public function getOnlineUsers(): Collection
    {
        return AuthMeUser::where('isLogged', true)
            ->orderBy('lastlogin', 'desc')
            ->get();
    }

    /**
     * Получить количество онлайн пользователей
     */
    public function getOnlineUsersCount(): int
    {
        return AuthMeUser::where('isLogged', true)->count();
    }

    /**
     * Получить общее количество пользователей
     */
    public function getTotalUsers(): int
    {
        return AuthMeUser::count();
    }

    /**
     * Получить количество зарегистрированных сегодня
     */
    public function getRegisteredToday(): int
    {
        $todayStart = Carbon::today()->timestamp * 1000;
        $todayEnd = Carbon::tomorrow()->timestamp * 1000;

        return AuthMeUser::whereBetween('regdate', [$todayStart, $todayEnd])->count();
    }

    /**
     * Получить количество зарегистрированных на этой неделе
     */
    public function getRegisteredThisWeek(): int
    {
        $weekStart = Carbon::now()->startOfWeek()->timestamp * 1000;
        $weekEnd = Carbon::now()->endOfWeek()->timestamp * 1000;

        return AuthMeUser::whereBetween('regdate', [$weekStart, $weekEnd])->count();
    }

    /**
     * Получить количество зарегистрированных в этом месяце
     */
    public function getRegisteredThisMonth(): int
    {
        $monthStart = Carbon::now()->startOfMonth()->timestamp * 1000;
        $monthEnd = Carbon::now()->endOfMonth()->timestamp * 1000;

        return AuthMeUser::whereBetween('regdate', [$monthStart, $monthEnd])->count();
    }

    /**
     * Получить количество активных пользователей сегодня
     */
    public function getActiveToday(): int
    {
        $todayStart = Carbon::today()->timestamp * 1000;
        $todayEnd = Carbon::tomorrow()->timestamp * 1000;

        return AuthMeUser::whereBetween('lastlogin', [$todayStart, $todayEnd])->count();
    }

    /**
     * Получить количество активных пользователей на этой неделе
     */
    public function getActiveThisWeek(): int
    {
        $weekStart = Carbon::now()->startOfWeek()->timestamp * 1000;
        $weekEnd = Carbon::now()->endOfWeek()->timestamp * 1000;

        return AuthMeUser::whereBetween('lastlogin', [$weekStart, $weekEnd])->count();
    }

    /**
     * Получить количество регистраций за конкретную дату
     */
    public function getRegistrationsForDate(Carbon $date): int
    {
        $dayStart = $date->startOfDay()->timestamp * 1000;
        $dayEnd = $date->endOfDay()->timestamp * 1000;

        return AuthMeUser::whereBetween('regdate', [$dayStart, $dayEnd])->count();
    }

    /**
     * Получить пользователей с пагинацией
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = AuthMeUser::query();

        if (!empty($filters['username'])) {
            $query->where('username', 'like', '%' . $filters['username'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['is_online'])) {
            $query->where('isLogged', $filters['is_online']);
        }

        if (!empty($filters['world'])) {
            $query->where('world', $filters['world']);
        }

        if (!empty($filters['registered_from'])) {
            $query->where('regdate', '>=', Carbon::parse($filters['registered_from'])->timestamp * 1000);
        }

        if (!empty($filters['registered_to'])) {
            $query->where('regdate', '<=', Carbon::parse($filters['registered_to'])->timestamp * 1000);
        }

        return $query->orderBy('regdate', 'desc')->paginate($perPage);
    }

    /**
     * Поиск пользователей
     */
    public function search(string $query, int $limit = 10): Collection
    {
        return AuthMeUser::where('username', 'like', "%{$query}%")
            ->orWhere('realname', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Получить последних зарегистрированных пользователей
     */
    public function getLatestRegistered(int $limit = 10): Collection
    {
        return AuthMeUser::orderBy('regdate', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить пользователей по миру
     */
    public function getUsersByWorld(string $world): Collection
    {
        return AuthMeUser::where('world', $world)->get();
    }

    /**
     * Получить уникальные миры
     */
    public function getUniqueWorlds(): array
    {
        return AuthMeUser::distinct()
            ->whereNotNull('world')
            ->pluck('world')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Получить топ игроков по времени последнего входа
     */
    public function getTopPlayersByLastLogin(int $limit = 10): Collection
    {
        return AuthMeUser::where('lastlogin', '>', 0)
            ->orderBy('lastlogin', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить неактивных пользователей
     */
    public function getInactiveUsers(int $daysInactive = 30): Collection
    {
        $threshold = Carbon::now()->subDays($daysInactive)->timestamp * 1000;

        return AuthMeUser::where('lastlogin', '<', $threshold)
            ->orWhere('lastlogin', 0)
            ->get();
    }

    /**
     * Получить статистику по мирам
     */
    public function getWorldStats(): array
    {
        return AuthMeUser::select('world')
            ->selectRaw('COUNT(*) as user_count')
            ->selectRaw('SUM(CASE WHEN isLogged = 1 THEN 1 ELSE 0 END) as online_count')
            ->whereNotNull('world')
            ->groupBy('world')
            ->orderBy('user_count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'world' => $item->world,
                    'total_users' => $item->user_count,
                    'online_users' => $item->online_count,
                    'percentage' => round(($item->user_count / $this->getTotalUsers()) * 100, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Получить топ игроков по времени игры
     */
    public function getTopPlayersByPlaytime(int $limit = 10): array
    {
        return AuthMeUser::select('username', 'realname', 'lastlogin', 'regdate')
            ->where('lastlogin', '>', 0)
            ->orderBy('lastlogin', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'username' => $user->username,
                    'realname' => $user->realname,
                    'last_login' => $user->getFormattedLastLogin(),
                    'registered' => $user->getFormattedRegDate(),
                    'playtime_hours' => 0 // Заглушка для времени игры
                ];
            })
            ->toArray();
    }

    /**
     * Массовое обновление пользователей
     */
    public function bulkUpdate(array $userIds, array $data): int
    {
        return AuthMeUser::whereIn('id', $userIds)->update($data);
    }

    /**
     * Получить пользователей с определенным IP
     */
    public function getUsersByIp(string $ip): Collection
    {
        return AuthMeUser::where('ip', $ip)
            ->orWhere('regip', $ip)
            ->get();
    }

    /**
     * Проверить существование пользователя
     */
    public function exists(string $username): bool
    {
        return AuthMeUser::where('username', strtolower($username))->exists();
    }

    /**
     * Получить количество пользователей в диапазоне дат
     */
    public function getCountByDateRange(Carbon $startDate, Carbon $endDate): int
    {
        $start = $startDate->timestamp * 1000;
        $end = $endDate->timestamp * 1000;

        return AuthMeUser::whereBetween('regdate', [$start, $end])->count();
    }
}
