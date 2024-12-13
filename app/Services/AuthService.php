<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ServiceResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    use ServiceResponse;

    public function login(array $validatedData): array
    {
        // 進入DB透過email跟密碼搜尋使用者
        if (! $this->attemptLogin($validatedData)) {
            return $this->formatResponse('error', 'Incorrect username or password', Response::HTTP_UNAUTHORIZED);
        }

        // 取得目前登入的使用者
        /** @var User $user */
        $user = auth()->user();

        // 檢查使用者是否已驗證電子郵件
        if (! $user->hasVerifiedEmail()) {
            return $this->formatResponse('error', 'Please verify your email first.', Response::HTTP_UNAUTHORIZED);
        }

        return $this->formatResponseWithToken('success', 'Authenticated', Response::HTTP_OK, ['token' => $this->createTokenForUser($user)->plainTextToken]);
    }

    private function attemptLogin(array $validatedData): bool
    {
        return Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']]);
    }

    private function createTokenForUser(User $user): NewAccessToken
    {
        return $user->createToken('API Token for'.$user->email, ['*'], now()->addMonth());
    }
}
