<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Traits\ApiResponse;
use App\Services\RegisterService;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    use ApiResponse;
    private RegisterService $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }
    public function register(RegisterRequest $request)
    {
        try{
            $validatedData = $request->validated();
            $result = $this->registerService->registerUser($validatedData);

            if($result['status'] === 'success'){
                return $this->success($result['message'], $result['statusCode']);
            }else{
                return $this->error($result['message'], $result['statusCode']);
            }
        }catch(\Exception $e){
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}