<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ServiceResponse;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationService
{
    use ServiceResponse;

    public function verifyEmail(int $id, string $hash): array
    {
        // 進入DB並透過ID搜尋使用者
        $user = $this->findUserById($id);

        // 驗證使用者 ID 和 hash 是否匹配
        if (! $this->verifyEmailHash($user, $hash)) {
            return $this->formatResponse('error', 'Invalid email verification link.', Response::HTTP_UNAUTHORIZED);
        }

        // 判斷使用者是否已經驗證過
        if ($this->isEmailAlreadyVerified($user)) {
            return $this->formatResponse('error', 'Email already verified.', Response::HTTP_CONFLICT);
        }

        // 將使用者的 email_verified_at 欄位標記為現在的時間
        $user->markEmailAsVerified();

        return $this->formatResponse('success', 'Email verified successfully.', Response::HTTP_OK);
    }

    private function findUserById(int $id): User|array
    {
        return User::findOrFail($id);
    }

    private function verifyEmailHash(User $user, string $hash): bool
    {
        return hash_equals($hash, sha1($user->getEmailForVerification()));
    }

    private function isEmailAlreadyVerified(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }
}
