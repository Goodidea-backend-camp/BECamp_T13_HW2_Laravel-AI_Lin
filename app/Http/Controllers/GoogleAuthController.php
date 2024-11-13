<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetupGoogleUserRequest;
use App\Services\GoogleAuthService;
use App\Traits\ApiResponse;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthController extends Controller
{
    use ApiResponse;

    public function __construct(private GoogleAuthService $googleAuthService)
    {
    }

    //重定向至Google登入頁面
    public function redirectToGoogle(): Response
    {
        try {
            //參考https://laravel.com/docs/11.x/socialite#stateless-authentication中關於無狀態Api的用法
            return Socialite::driver('google')->stateless()->redirect();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function handleGoogleCallback(): Response
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        //使用GoogleAuthService處理Google使用者資料
        $result = $this->googleAuthService->handleCallback($googleUser);

        if ($result['status'] === 'success') {
            return isset($result['data'])
                ? $this->responseWithToken($result['message'], $result['data'], $result['statusCode'])
                : $this->success($result['message'], $result['statusCode']);
        }

        return $this->error($result['message'], $result['statusCode']);

    }

    //使用者使用Google第三方登入初次註冊帳號時，需補填自我介紹
    public function handleGoogleSetup(SetupGoogleUserRequest $setupGoogleUserRequest): Response
    {
        $validatedData = $setupGoogleUserRequest->validated();
        $user = auth()->user();

        //使用GoogleAuthService處理Google使用者補填自我介紹
        $result = $this->googleAuthService->setupSelfProfile($user, $validatedData['self_profile']);

        return $result['status'] === 'success'
            ? $this->success($result['message'], $result['statusCode'])
            : $this->error($result['message'], $result['statusCode']);

    }
}
