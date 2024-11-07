<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function success(string $message, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ], $statusCode);

    }

    public function error(string $message, int $statuscode = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statuscode);
    }

    public function responseWithToken(string $message, array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
