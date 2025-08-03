<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AuthMeAuthException extends Exception
{
    protected int $statusCode;

    public function __construct(string $message = 'Authentication failed', int $statusCode = 401, int $code = 0)
    {
        parent::__construct($message, $code);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->getCode()
        ], $this->statusCode);
    }
}
