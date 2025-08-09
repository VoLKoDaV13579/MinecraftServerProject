<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class RateLimitException extends Exception
{
    private int $retryAfter;

    public function __construct(string $message, int $retryAfter = 60)
    {
        parent::__construct($message, 429);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'retry_after' => $this->retryAfter
        ], 429)->header('Retry-After', $this->retryAfter);
    }
}
