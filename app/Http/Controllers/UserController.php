<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $userService)
    {

    }

    public function show(int $userId): JsonResponse|UserResource
    {
        $result = $this->userService->getUserData($userId);

        return new UserResource($result);
    }

    public function update(UpdateUserRequest $updateUserRequest, int $userId): JsonResponse|UserResource
    {
        $validatedData = $updateUserRequest->validated();
        $result = $this->userService->updateUserData($userId, $validatedData);

        return new UserResource($result);
    }
}
