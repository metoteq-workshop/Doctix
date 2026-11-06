<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company',
        'job_title',
        'phone',
        'country',
        'timezone',
        'language',
        'bio',
        'preferences',
    ];

    protected function casts(): array
    {
        return ['preferences' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

