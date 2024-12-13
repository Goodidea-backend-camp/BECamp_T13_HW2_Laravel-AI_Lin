<?php

namespace App\Traits;

trait ServiceResponse
{
    public function formatResponse(string $status, string $message, int $statusCode): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'statusCode' => $statusCode,
        ];
    }

    public function formatResponseWithToken(string $status, string $message, int $statusCode, array $data = []): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'statusCode' => $statusCode,
            'data' => $data,
        ];
    }
}
