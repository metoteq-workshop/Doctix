<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'module',
        'action',
        'target_type',
        'target_id',
        'ip_address',
        'severity',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function logEvent(
        string $module,
        string $action,
        ?int $actorId = null,
        ?string $targetType = null,
        ?int $targetId = null,
        array $oldValues = [],
        array $newValues = [],
        string $severity = 'info',
        ?string $ip = null
    ): self {
        return static::create([
            'actor_id' => $actorId ?? auth()->id(),
            'module' => $module,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => $ip ?? request()?->ip(),
            'severity' => $severity,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
        ]);
    }
}

