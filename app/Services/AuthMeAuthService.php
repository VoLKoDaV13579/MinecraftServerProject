<?php

namespace App\Services;

use App\Models\AuthMeUser;
use App\Repositories\AuthMeUserRepository;
use App\Services\AuthMePasswordService;
use App\Exceptions\AuthMeAuthException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AuthMeAuthService
{
    public function __construct(
        private AuthMeUserRepository  $userRepository,
        private AuthMePasswordService $passwordService
    )
    {
    }

    /**
     * Регистрация нового пользователя
     */
    public function register(array $data, Request $request): AuthMeUser
    {
        if ($this->userRepository->findByUsername($data['username'])) {
            throw new AuthMeAuthException('Пользователь с таким именем уже существует');
        }

        if (!empty($data['email']) && $this->userRepository->findByEmail($data['email'])) {
            throw new AuthMeAuthException('Пользователь с таким email уже существует');
        }

        $userData = [
            'username' => strtolower($data['username']),
            'realname' => $data['realname'] ?? $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $this->passwordService->hash($data['password'], $data['username']),
            'regdate' => time() * 1000,
            'regip' => $request->ip(),
            'ip' => $request->ip(),
            'lastlogin' => 0,
            'x' => 0,
            'y' => 0,
            'z' => 0,
            'world' => 'world',
            'yaw' => 0,
            'pitch' => 0,
            'isLogged' => false
        ];

        return $this->userRepository->create($userData);
    }

    /**
     * Авторизация пользователя
     */
    public function login(array $credentials, Request $request): array
    {
        $this->ensureNotRateLimited($credentials['username'], $request);

        $user = $this->userRepository->findByUsername($credentials['username']);

        if (!$user || !$this->passwordService->verify($credentials['password'], $user->password, $user->username)) {
            RateLimiter::hit($this->throttleKey($credentials['username'], $request));
            throw new AuthMeAuthException('Неверные учетные данные');
        }

        RateLimiter::clear($this->throttleKey($credentials['username'], $request));

        $this->userRepository->update($user, [
            'lastlogin' => time() * 1000,
            'ip' => $request->ip()
        ]);

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token
        ];
    }

    /**
     * Обновление профиля пользователя
     */
    public function updateProfile(AuthMeUser $user, array $data): AuthMeUser
    {
        $updateData = [];

        if (isset($data['realname'])) {
            $updateData['realname'] = $data['realname'];
        }

        if (isset($data['email'])) {
            $existingUser = $this->userRepository->findByEmail($data['email']);
            if ($existingUser && $existingUser->id !== $user->id) {
                throw new AuthMeAuthException('Email уже используется другим пользователем');
            }
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            if (isset($data['current_password'])) {
                if (!$this->passwordService->verify($data['current_password'], $user->password, $user->username)) {
                    throw new AuthMeAuthException('Неверный текущий пароль');
                }
            }

            $updateData['password'] = $this->passwordService->hash($data['password'], $user->username);
        }

        if (!empty($updateData)) {
            $this->userRepository->update($user, $updateData);
        }

        return $user->fresh();
    }

    /**
     * Смена пароля
     */
    public function changePassword(AuthMeUser $user, string $currentPassword, string $newPassword): bool
    {
        if (!$this->passwordService->verify($currentPassword, $user->password, $user->username)) {
            throw new AuthMeAuthException('Неверный текущий пароль');
        }

        if ($currentPassword === $newPassword) {
            throw new AuthMeAuthException('Новый пароль должен отличаться от текущего');
        }

        $newHashedPassword = $this->passwordService->hash($newPassword, $user->username);

        return $this->userRepository->update($user, [
            'password' => $newHashedPassword
        ]);
    }

    /**
     * Проверка rate limiting
     */
    private function ensureNotRateLimited(string $username, Request $request): void
    {
        $key = $this->throttleKey($username, $request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw new AuthMeAuthException(
                "Слишком много попыток входа. Попробуйте снова через {$seconds} секунд."
            );
        }
    }

    /**
     * Генерация ключа для rate limiting
     */
    private function throttleKey(string $username, Request $request): string
    {
        return strtolower($username) . '|' . $request->ip();
    }
}
