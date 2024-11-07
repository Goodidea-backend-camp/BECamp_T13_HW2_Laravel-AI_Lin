<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserService
{
    use AuthorizesRequests;

    public function getUserData(int $userId): User
    {
        $user = User::findorfail($userId);

        $this->authorize('view', $user);

        return $user;
    }

}
