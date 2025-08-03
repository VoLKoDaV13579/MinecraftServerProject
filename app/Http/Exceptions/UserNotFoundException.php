<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UserNotFoundException extends Exception
{
    public function __construct(string $identifier = '')
    {
        $message = $identifier
            ? "Пользователь '{$identifier}' не найден"
            : 'Пользователь не найден';

        parent::__construct($message, 404);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'USER_NOT_FOUND'
        ], 404);
    }
}
