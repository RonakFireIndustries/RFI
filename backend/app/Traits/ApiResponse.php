<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a standard success response.
     */
    protected function success(string $message, mixed $data = [], mixed $meta = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ], $statusCode);
    }

    /**
     * Send a standard error response.
     */
    protected function error(string $message, mixed $data = [], int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
            'meta'    => (object)[],
        ], $statusCode);
    }
}
