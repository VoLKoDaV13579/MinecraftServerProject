<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\OnlineUserResource;
use App\Http\Resources\StatsResource;
use App\Services\AuthMeStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(
        private AuthMeStatsService $statsService
    )
    {
    }

    public function index(): JsonResponse
    {
        $stats = $this->statsService->getGeneralStats();

        return $this->successResponse(new StatsResource(['server' => $stats]));
    }

    public function onlineUsers(): JsonResponse
    {
        $onlineUsers = $this->statsService->getOnlineUsers();

        return $this->successResponse([
            'online_users' => $onlineUsers,
            'count' => count($onlineUsers)
        ]);
    }

    public function registrationStats(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $days = min(max($days, 1), 365);

        $stats = $this->statsService->getRegistrationStats($days);

        return $this->successResponse([
            'registration_stats' => $stats,
            'period_days' => $days
        ]);
    }

    public function topPlayers(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $limit = min(max($limit, 1), 50);

        $topPlayers = $this->statsService->getTopPlayers($limit);

        return $this->successResponse([
            'top_players' => $topPlayers,
            'limit' => $limit
        ]);
    }

    public function activityStats(): JsonResponse
    {
        $stats = $this->statsService->getActivityStats();

        return $this->successResponse([
            'activity_stats' => $stats
        ]);
    }

    public function worldStats(): JsonResponse
    {
        $stats = $this->statsService->getWorldStats();

        return $this->successResponse([
            'world_stats' => $stats
        ]);
    }
}
