<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $userService)
    {

    }

    public function show(int $userId): JsonResponse|UserResource
    {
        try {
            $result = $this->userService->getUserData($userId);

            return new UserResource($result);
        } catch (ModelNotFoundException) {
            return $this->error('User not found', Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException) {
            return $this->error('Unauthorized', Response::HTTP_FORBIDDEN);
        }
    }

    public function update(UpdateUserRequest $request, int $userId): JsonResponse|UserResource
    {
        try {
            $validatedData = $request->validated();
            $result = $this->userService->updateUserData($userId, $validatedData);

            return new UserResource($result);
        } catch (ModelNotFoundException) {
            return $this->error('User not found', Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException) {
            return $this->error('Unauthorized', Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
