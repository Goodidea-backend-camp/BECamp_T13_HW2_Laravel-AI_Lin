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
        $user = $this->userService->getUserData($userId);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $updateUserRequest, int $userId): JsonResponse|UserResource
    {
        $validatedData = $updateUserRequest->validated();
        $user = $this->userService->updateUserData($userId, $validatedData);

        return new UserResource($user);
    }
}
