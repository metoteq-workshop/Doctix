<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'badge_text',
        'description',
        'price',
        'currency',
        'billing_interval',
        'interval_days',
        'is_unlimited',
        'daily_tool_limit',
        'max_file_size_mb',
        'priority_processing',
        'access_premium_tools',
        'access_ai_tools',
        'grace_period_days',
        'is_featured',
        'is_active',
        'sort_order',
        'stripe_price_id',
        'paypal_plan_id',
        'paystack_plan_code',
        'flutterwave_plan_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'interval_days' => 'integer',
            'is_unlimited' => 'boolean',
            'daily_tool_limit' => 'integer',
            'max_file_size_mb' => 'integer',
            'priority_processing' => 'boolean',
            'access_premium_tools' => 'boolean',
            'access_ai_tools' => 'boolean',
            'grace_period_days' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class)->orderBy('sort_order');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}

