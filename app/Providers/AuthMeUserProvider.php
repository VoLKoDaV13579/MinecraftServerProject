<?php

namespace App\Providers;

use App\Models\AuthMeUser;
use App\Services\AuthMePasswordService;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthMeUserProvider implements UserProvider
{
    public function __construct(
        private AuthMePasswordService $passwordService
    )
    {
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        return AuthMeUser::where('username', $identifier)->first();
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Не используется
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return AuthMeUser::where('username', strtolower($credentials['username']))->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $this->passwordService->verify(
            $credentials['password'],
            $user->getAuthPassword(),
            $user->username
        );
    }
    public function rehashPasswordIfRequired(Authenticatable $user, #[SensitiveParameter] array $credentials, bool $force = false): void
    {
        // Если у вас есть логика для пересоздания хеша пароля, добавьте её здесь
        // Например:
        // if ($force || $this->passwordService->needsRehash($user->getAuthPassword())) {
        //     $newHash = $this->passwordService->hash($credentials['password'], $user->username);
        //     $user->password = $newHash;
        //     $user->save();
        // }

        return;
    }
}
