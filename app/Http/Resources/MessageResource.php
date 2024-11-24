<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $role
 * @property string $content
 * @property string $file_path
 */
class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
            'file_path' => $this->file_path,
        ];
    }
}
