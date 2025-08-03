<?php

namespace App\Http\Controllers;

use App\Exceptions\UserNotFoundException;
use App\Http\Requests\Users\IndexRequest;
use App\Http\Requests\Users\SearchRequest;
use App\Http\Resources\PublicUserResource;
use App\Http\Resources\UserCollection;
use App\Repositories\AuthMeUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(
        private AuthMeUserRepository $userRepository
    )
    {
    }

    public function index(IndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;
        unset($filters['per_page']);

        $users = $this->userRepository->getPaginated($perPage, $filters);

        return $this->successResponse(new UserCollection($users));
    }

    public function show(string $username): JsonResponse
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            throw new UserNotFoundException($username);
        }

        return $this->successResponse([
            'user' => new PublicUserResource($user)
        ]);
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $query = $validated['q'];
        $limit = $validated['limit'] ?? 10;

        $users = $this->userRepository->search($query, $limit);

        return $this->successResponse([
            'users' => PublicUserResource::collection($users),
            'query' => $query,
            'count' => $users->count()
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 10), 20);

        $users = $this->userRepository->getLatestRegistered($limit);

        return $this->successResponse([
            'users' => PublicUserResource::collection($users),
            'count' => $users->count()
        ]);
    }

    public function byWorld(string $world): JsonResponse
    {
        $users = $this->userRepository->getUsersByWorld($world);

        return $this->successResponse([
            'users' => PublicUserResource::collection($users),
            'world' => $world,
            'count' => $users->count()
        ]);
    }

    public function worlds(): JsonResponse
    {
        $worlds = $this->userRepository->getUniqueWorlds();
        $worldStats = $this->userRepository->getWorldStats();

        return $this->successResponse([
            'worlds' => $worlds,
            'world_stats' => $worldStats
        ]);
    }
}
