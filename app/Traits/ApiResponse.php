<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($message, $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ], $statusCode);

    }

    public function error($message, $statuscode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statuscode);
    }

    public function responseWithToken($message, $data, $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
