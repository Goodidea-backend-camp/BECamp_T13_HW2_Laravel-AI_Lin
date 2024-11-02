<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Traits\ApiResponse;
use App\Services\RegisterService;
use Symfony\Component\HttpFoundation\Response;
use App\Services\EmailVerificationService;

class RegisterController extends Controller
{
    use ApiResponse;

    // Constructor Property Promotion 參考https://timjwilliams.medium.com/php-8-constructor-property-promotion-simplifying-class-definitions-with-examples-6039ea2f09b7
    public function __construct(
        private RegisterService $registerService,
        private EmailVerificationService $emailVerificationService
    ) {}
    public function register(RegisterRequest $request)
    {
        try {
            $validatedData = $request->validated();

            //將請求資料透過 RegisterService 進行處理
            $result = $this->registerService->registerUser($validatedData);

            return $result['status'] === 'success'
                ? $this->success($result['message'], $result['statusCode'])
                : $this->error($result['message'], $result['statusCode']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifyEmail($id, $hash)
    {
        try {
            $result = $this->emailVerificationService->verifyEmail($id, $hash);

            return $result['status'] === 'success'
                ? redirect('/')
                : $this->error($result['message'], $result['statusCode']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
