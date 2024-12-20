<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\EmailVerificationService;
use App\Services\RegisterService;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    use ApiResponse;

    // Constructor Property Promotion 參考https://timjwilliams.medium.com/php-8-constructor-property-promotion-simplifying-class-definitions-with-examples-6039ea2f09b7
    public function __construct(
        private RegisterService $registerService,
        private EmailVerificationService $emailVerificationService
    ) {
    }

    public function register(RegisterRequest $registerRequest): Response
    {
        $validatedData = $registerRequest->validated();

        //將請求資料透過 RegisterService 進行處理
        $result = $this->registerService->registerUser($validatedData);

        return $result['status'] === 'success'
            ? $this->success($result['message'], $result['statusCode'])
            : $this->error($result['message'], $result['statusCode']);
    }

    public function verifyEmail(int $id, string $hash): Response
    {
        $result = $this->emailVerificationService->verifyEmail($id, $hash);

        return $result['status'] === 'success'
            ? redirect('/')
            : $this->error($result['message'], $result['statusCode']);
    }
}
