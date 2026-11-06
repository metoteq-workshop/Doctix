<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(?int $userId, string $eventType, string $description, array $metadata = [], ?string $ip = null): self
    {
        return static::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'description' => $description,
            'ip_address' => $ip ?? request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 250),
            'metadata' => $metadata,
        ]);
    }
}

