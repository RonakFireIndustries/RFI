<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiResponseWrapper
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only wrap JSON responses
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            // If already wrapped, return as-is
            if (is_array($data) && array_key_exists('success', $data) && array_key_exists('message', $data) && array_key_exists('data', $data)) {
                return $response;
            }

            $status = $response->getStatusCode();
            if ($status >= 200 && $status < 300) {
                $wrapped = [
                    'success' => true,
                    'message' => isset($data['message']) ? $data['message'] : 'Success',
                    'data' => $data,
                ];
                return new JsonResponse($wrapped, $status, $response->headers->all());
            }

            // Errors
            $wrapped = [
                'success' => false,
                'message' => $data['message'] ?? ($response->statusText ?? 'Error'),
                'data' => $data,
            ];
            return new JsonResponse($wrapped, $status, $response->headers->all());
        }

        return $response;
    }
}
