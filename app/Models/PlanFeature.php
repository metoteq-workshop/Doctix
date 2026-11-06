<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $fillable = [
        'subscription_plan_id',
        'feature_name',
        'feature_value',
        'is_included',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_included' => 'boolean'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}

