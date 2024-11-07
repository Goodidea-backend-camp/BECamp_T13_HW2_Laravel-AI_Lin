<?php

namespace App\Services;

use App\AI\Assistant;
use App\Models\User;
use App\Traits\ServiceResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterService
{
    use ServiceResponse;

    public function __construct(private Assistant $assistant)
    {
    }

    public function registerUser(array $validatedData): array
    {
        try {
            // 確認信箱是否已經被註冊
            $existingUser = $this->findExistingUserByEmail($validatedData['email']);

            // 信箱已經被註冊
            if ($existingUser) {
                return $this->handleExistingUserPassword($existingUser, $validatedData['password']);
            }

            // 確認使用者名稱是否符合善良風俗
            $usernameValidationResult = $this->validateUserName($validatedData['name']);

            if (! is_null($usernameValidationResult)) {
                return $this->formatResponse('error', $usernameValidationResult['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            //將使用者自我介紹透過 openai 的 Image API 來生成大頭貼
            $profile_image_path = $this->generateProfileImage($validatedData['self_profile']);

            // 將使用者資料存入資料庫
            $user = $this->storeNewUser($validatedData, $profile_image_path);

            // 寄送驗證信
            $user->sendEmailVerificationNotification();

            return $this->formatResponse('success', 'Register successful.Please check your email for verification.', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->formatResponse('error', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function findExistingUserByEmail(string $email): ?user
    {
        return User::firstWhere('email', $email);
    }

    private function handleExistingUserPassword(User $existingUser, string $password): array
    {
        // 信箱已經被註冊，但是密碼欄位不為空值，回傳錯誤（代表首次註冊是本地註冊）
        if (! empty($password)) {
            return $this->formatResponse('error', 'The email has already been taken.', Response::HTTP_CONFLICT);
        }

        // 信箱已經被註冊，但是密碼欄位為空值，更新密碼（代表首次註冊是使用第三方登入）
        $existingUser->password = $password;
        $existingUser->save();

        return $this->formatResponse('success', 'Password updated successful!', Response::HTTP_OK);

    }

    // 檢查使用者名稱是否符合善良風俗
    private function validateUserName(string $username): ?array
    {
        // 如果使用者名稱不符合善良風俗，回傳錯誤，如果符合善良風俗，回傳null
        if (! $this->assistant->isUsernameDecent($username)) {
            return $this->formatResponse('error', 'The name is not acceptable.Please try another name.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }

    private function generateProfileImage(string $selfProfile): string
    {
        return $this->assistant->visualize($selfProfile);
    }

    private function storeNewUser(array $validatedData, string $profileImagePath): User
    {

        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = $validatedData['password'];
        $user->self_profile = $validatedData['self_profile'];
        $user->profile_image_path = $profileImagePath;
        $user->save();

        return $user;

    }
}
