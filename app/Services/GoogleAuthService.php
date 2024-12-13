<?php

namespace App\Services;

use App\AI\Assistant;
use App\Models\User;
use App\Models\UserSocialAccount;
use App\Traits\ServiceResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthService
{
    use ServiceResponse;

    private const PROVIDER_NAME = 'Google';

    public function __construct(private Assistant $assistant)
    {
    }

    //處理 Google 第三方登入資料
    public function handleCallback(SocialiteUser $socialiteUser): array
    {
        //查看第三方登入表是否已建立該使用者
        $existingUserSocialAccount = $this->findExistingUserSocailAccount($socialiteUser);

        // 如果存在，直接登入並發給token
        if ($existingUserSocialAccount instanceof UserSocialAccount) {
            return $this->loginExistingUser($existingUserSocialAccount->user);
        }

        // 第三方登入表不存在資料，檢查是否有使用本地註冊
        $existingUser = $this->findExistingUser($socialiteUser->getEmail());

        // 如果有使用本地註冊，在第三方登入表建立資料並與User表建立關聯，並進行登入
        if ($existingUser instanceof User) {
            $this->linkSocialAccountToUser($existingUser, $socialiteUser);

            return $this->loginExistingUser($existingUser);
        }

        // 如果都沒有資料，則建立新帳號
        return $this->createNewUser($socialiteUser);
    }

    //使用者使用Google第三方登入初次註冊帳號時，需補填自我介紹
    public function setupSelfProfile(User $user, string $selfProfile): array
    {
        $profileImagePath = $this->assistant->visualize($selfProfile);

        $user->self_profile = $selfProfile;
        $user->profile_image_path = $profileImagePath;
        $user->save();

        return $this->formatResponse('success', 'Self Profile created successfully', Response::HTTP_OK);
    }

    // 查看第三方登入表是否已建立該使用者
    private function findExistingUserSocailAccount(SocialiteUser $socialiteUser): ?UserSocialAccount
    {
        return UserSocialAccount::where('provider_id', $socialiteUser->getId())
            ->where('provider', self::PROVIDER_NAME)
            ->first();
    }

    // 查看User表是否已建立該使用者
    private function findExistingUser(string $email): ?user
    {
        /** @var User|null $user */
        $user = User::firstWhere('email', $email);

        return $user;
    }

    // 若是已建立帳號的使用者，直接登入並發給token
    private function loginExistingUser(User $user): array
    {
        Auth::login($user);

        return $this->formatResponseWithToken('success', 'Authenticated', Response::HTTP_OK,
            ['token' => $this->createTokenForUser($user)->plainTextToken]
        );
    }

    // 將第三方登入資料與User表建立關聯
    private function linkSocialAccountToUser(User $user, SocialiteUser $socialiteUser): void
    {
        $userSocialAccount = new UserSocialAccount();
        $userSocialAccount->provider_id = $socialiteUser->getId();
        $userSocialAccount->provider = self::PROVIDER_NAME;
        $userSocialAccount->email = $socialiteUser->getEmail();
        $userSocialAccount->user_id = $user->id;
        $userSocialAccount->save();
    }

    // 新的使用者使用Google第三方登入註冊時，建立新帳號，並發給token
    private function createNewUser(SocialiteUser $socialiteUser): array
    {
        $user = new User();
        $user->name = $socialiteUser->getName();
        $user->email = $socialiteUser->getEmail();
        $user->email_verified_at = now();
        $user->self_profile = '';
        $user->profile_image_path = '';
        $user->save();

        $userSocialAccount = new UserSocialAccount();
        $userSocialAccount->provider_id = $socialiteUser->getId();
        $userSocialAccount->provider = self::PROVIDER_NAME;
        $userSocialAccount->email = $socialiteUser->getEmail();
        $userSocialAccount->user_id = $user->id;
        $userSocialAccount->save();

        Auth::login($user);

        return $this->formatResponseWithToken('success', 'Please fill in the self profile.', Response::HTTP_OK,
            ['token' => $this->createTokenForUser($user)->plainTextToken]
        );
    }

    // 建立token
    private function createTokenForUser(User $user): NewAccessToken
    {
        return $user->createToken('API Token for '.$user->email, ['*'], now()->addMonth());
    }
}
