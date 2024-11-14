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

    // 取得使用者資料
    public function getUserData(int $userId): User
    {
        $user = User::findorfail($userId);

        $this->authorize('view', $user);

        return $user;
    }

    // 更新使用者資料（名稱、自我介紹），並根據更新後自我介紹生成新的大頭貼
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

    // 將使用者的自我介紹透過 openai 的 Image API 來生成大頭貼
    private function generateProfileImage(string $selfProfile): string
    {
        return $this->assistant->visualize($selfProfile);
    }
}
