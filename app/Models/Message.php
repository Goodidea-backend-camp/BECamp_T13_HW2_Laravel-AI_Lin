<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $thread_id
 * @property int $role
 * @property string $type
 * @property string $content
 * @property string $file_path
 * @property-read \App\Models\Thread $thread
 *
 * @method static Builder|Message where(string $column, mixed $value)
 */
class Message extends Model
{
    use HasFactory;

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }
}
