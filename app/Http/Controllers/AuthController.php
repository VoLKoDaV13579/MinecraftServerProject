<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthMeAuthException;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthMeAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            // Логируем системные ошибки
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

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
        } catch (\Exception $e) {
            RateLimiter::hit($request->throttleKey());
            Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'email' => $request->input('email')
            ]);

            return $this->errorResponse('Ошибка при входе в систему', 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return $this->dataResponse([
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
            Log::error('Profile update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse('Ошибка при обновлении профиля', 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $this->authService->changePassword(
                $request->user(),
                $validatedData['current_password'],
                $validatedData['password']
            );

            // Удаляем все токены пользователя
            $request->user()->tokens()->delete();

            return $this->successResponse([
                'message' => 'Пароль успешно изменен. Выполните вход заново.'
            ]);
        } catch (AuthMeAuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Password change error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse('Ошибка при изменении пароля', 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                $token->delete();
            }

            return $this->successResponse([
                'message' => 'Успешный выход из системы'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse('Ошибка при выходе из системы', 500);
        }
    }

    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse([
                'message' => 'Выход выполнен на всех устройствах'
            ]);
        } catch (\Exception $e) {
            Log::error('LogoutAll error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse('Ошибка при выходе из всех устройств', 500);
        }
    }

    public function checkToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $token = $user->currentAccessToken();

            if (!$token) {
                return $this->errorResponse('Токен не найден', 401);
            }

            return $this->successResponse([
                'valid' => true,
                'user' => new UserResource($user),
                'token_name' => $token->name,
                'expires_at' => $token->expires_at
            ]);
        } catch (\Exception $e) {
            Log::error('CheckToken error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse('Ошибка при проверке токена', 500);
        }
    }
}
