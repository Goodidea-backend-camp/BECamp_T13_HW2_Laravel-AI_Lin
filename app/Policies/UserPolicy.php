<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        // $authUser是Laravel根據token解析出來的使用者
        return $authUser->id === $targetUser->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->id === $targetUser->id;
    }
}
