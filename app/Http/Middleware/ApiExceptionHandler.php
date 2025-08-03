<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if(!$response->expectsJson())
        {
            return $response;
        }
        if ($response instanceof JsonResponse && !this->isSuccessResponse($response)) {
            return $response;
        }

        return $response;
    }


    private function isSuccessResponse(JsonResponse $response): bool
    {
        $data  =$response->getData(true);
        return isset($data['success']) && $data['success'] === true;
    }
}
