<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $password
 * @property string $self_profile
 * @property string $profile_image_path
 * @property bool $is_premium
 * @property \Illuminate\Support\Carbon $email_verified_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User firstWhere(string $column, mixed $value)
 * @method static User|Builder findOrFail(int $id)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // 隱藏的欄位
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // 將 email_verified_at 轉為 datetime,password 轉為 hash值
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userSocialAccounts(): HasMany
    {
        return $this->hasMany(UserSocialAccount::class);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }
}
