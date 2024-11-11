<?php

namespace App\Services;

use App\AI\Assistant;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserService
{
    use AuthorizesRequests;

    public function __construct(private Assistant $assistant)
    {
    }

    public function getUserData(int $userId): User
    {
        $user = User::findorfail($userId);

        $this->authorize('view', $user);

        return $user;
    }

    public function updateUserData(int $userId, array $data): User
    {
        $user = User::findorfail($userId);

        $this->authorize('update', $user);

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['self_profile'])) {
            $profile_image_path = $this->generateProfileImage($data['self_profile']);
            $user->self_profile = $data['self_profile'];
            $user->profile_image_path = $profile_image_path;
        }

        $user->save();

        return $user;
    }

    private function generateProfileImage(string $selfProfile): string
    {
        return $this->assistant->visualize($selfProfile);
    }
}
