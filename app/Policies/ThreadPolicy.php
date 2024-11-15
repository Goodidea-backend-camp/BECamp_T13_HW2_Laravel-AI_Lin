<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id;
    }
}
