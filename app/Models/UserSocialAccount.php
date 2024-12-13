<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $provider_id
 * @property string $provider
 * @property string $email
 * @property int $user_id
 * @property-read \App\Models\User $user
 *
 * @method static Builder|UserSocialAccount where(string $column, mixed $value)
 */
class UserSocialAccount extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
