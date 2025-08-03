<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthMeAuthService;
use App\Exceptions\AuthMeAuthException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(
        private AuthMeAuthService $authService
    )
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated(), $request);

            return $this->successResponse([
                'user' => new UserResource($user),
                'message' => 'Пользователь успешно зарегистрирован'
            ], 201);

        } catch (AuthMeAuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка при регистрации', 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $request->ensureIsNotRateLimited();

            $result = $this->authService->login($request->validated(), $request);

            RateLimiter::clear($request->throttleKey());

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration', 43200),
                'message' => 'Успешная авторизация'
            ]);

        } catch (AuthMeAuthException $e) {
            RateLimiter::hit($request->throttleKey());
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => new UserResource($request->user())
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->updateProfile(
                $request->user(),
                $request->validated()
            );

            return $this->successResponse([
                'user' => new UserResource($user),
                'message' => 'Профиль успешно обновлен'
            ]);

        } catch (AuthMeAuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка при обновлении профиля', 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword(
                $request->user(),
                $request->validated()['current_password'],
                $request->validated()['password']
            );

            $request->user()->tokens()->delete();

            return $this->successResponse([
                'message' => 'Пароль успешно изменен. Выполните вход заново.'
            ]);

        } catch (AuthMeAuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse([
            'message' => 'Успешный выход из системы'
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse([
            'message' => 'Выход выполнен на всех устройствах'
        ]);
    }

    public function checkToken(Request $request): JsonResponse
    {
        return $this->successResponse([
            'valid' => true,
            'user' => new UserResource($request->user()),
            'token_name' => $request->user()->currentAccessToken()->name,
            'expires_at' => $request->user()->currentAccessToken()->expires_at
        ]);
    }
}
