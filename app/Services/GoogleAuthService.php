<?php

namespace App\Services;

use App\AI\Assistant;
use App\Models\User;
use App\Models\UserSocialAccount;
use App\Traits\ServiceResponse;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthService
{
    use ServiceResponse;

    private const PROVIDER_NAME = 'Google';

    public function __construct(private Assistant $assistant)
    {
    }

    // 處理Google第三方登入資料
    public function handleCallback($googleUser): array
    {
        try {
            //查看第三方登入表是否已建立該使用者
            $existingUserSocialAccount = $this->findExistingUserSocailAccount($googleUser);

            // 如果存在，直接登入並發給token
            if ($existingUserSocialAccount) {
                return $this->loginExistingUser($existingUserSocialAccount->user);
            }
            // 第三方登入表不存在資料，檢查是否有使用本地註冊
            $existingUser = $this->findExistingUser($googleUser->email);

            // 如果有使用本地註冊，在第三方登入表建立資料並與User表建立關聯，並進行登入
            if ($existingUser) {
                $this->linkSocialAccountToUser($existingUser, $googleUser);

                return $this->loginExistingUser($existingUser);
            }

            // 如果都沒有資料，则建立新帳號
            return $this->createNewUser($googleUser);
        } catch (\Exception $e) {
            return $this->formatResponse('error', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // 使用者使用Google第三方登入初次註冊帳號時，需補填自我介紹
    public function setupSelfProfile($user, $selfProfile): array
    {
        try {
            $profileImagePath = $this->assistant->visualize($selfProfile);

            $user->self_profile = $selfProfile;
            $user->profile_image_path = $profileImagePath;
            $user->save();

            return $this->formatResponse('success', 'Self Profile created successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->formatResponse('error', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function findExistingUserSocailAccount($googleUser): ?UserSocialAccount
    {
        return UserSocialAccount::firstWhere([
            'provider_id' => $googleUser->id,
            'provider' => self::PROVIDER_NAME,
        ]);
    }

    private function findExistingUser($email): ?user
    {
        return User::firstWhere('email', $email);
    }

    // 若是已建立帳號的使用者，直接登入並發給token
    private function loginExistingUser($user)
    {
        auth()->login($user);

        return $this->formatResponseWithToken('success', 'Authenticated', Response::HTTP_OK,
            ['token' => $this->createTokenForUser($user)->plainTextToken]
        );
    }

    private function linkSocialAccountToUser(User $user, $googleUser)
    {
        $userSocialAccount = new UserSocialAccount();
        $userSocialAccount->provider_id = $googleUser->id;
        $userSocialAccount->provider = self::PROVIDER_NAME;
        $userSocialAccount->email = $googleUser->email;
        $userSocialAccount->user_id = $user->id;
        $userSocialAccount->save();
    }

    // 新的使用者使用Google第三方登入註冊時，建立新帳號，並發給token
    private function createNewUser($googleUser)
    {
        $user = new User();
        $user->name = $googleUser->name;
        $user->email = $googleUser->email;
        $user->email_verified_at = now();
        $user->self_profile = '';
        $user->profile_image_path = '';
        $user->save();

        $userSocialAccount = new UserSocialAccount();
        $userSocialAccount->provider_id = $googleUser->id;
        $userSocialAccount->provider = self::PROVIDER_NAME;
        $userSocialAccount->email = $googleUser->email;
        $userSocialAccount->user_id = $user->id;
        $userSocialAccount->save();

        auth()->login($user);

        return $this->formatResponseWithToken('success', 'Please fill in the self profile.', Response::HTTP_OK,
            ['token' => $this->createTokenForUser($user)->plainTextToken]
        );
    }

    private function createTokenForUser($user)
    {
        return $user->createToken('API Token for '.$user->email, ['*'], now()->addMonth());
    }
}
