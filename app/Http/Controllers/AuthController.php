<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {
    }

    public function login(LoginRequest $loginRequest): JsonResponse
    {
        $validatedData = $loginRequest->validated();
        // 將請求資料透過 AuthService 進行處理
        $result = $this->authService->login($validatedData);

        return $result['status'] === 'success'
            ? $this->responseWithToken($result['message'], $result['data'], $result['statusCode'])
            : $this->error($result['message'], $result['statusCode']);

    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logout successfully', Response::HTTP_OK);
    }
}
