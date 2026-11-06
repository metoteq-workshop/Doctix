<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'subscription_uuid',
        'user_id',
        'subscription_plan_id',
        'status',
        'payment_gateway',
        'gateway_subscription_id',
        'amount',
        'currency',
        'auto_renew',
        'starts_at',
        'ends_at',
        'grace_ends_at',
        'canceled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'auto_renew' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isValid(): bool
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        if (!in_array($this->status, ['active', 'grace_period', 'canceled'], true)) {
            return false;
        }

        if ($this->ends_at === null) {
            return true;
        }

        if ($this->ends_at->greaterThan($now)) {
            return true;
        }

        if ($this->status === 'grace_period' && $this->grace_ends_at && $this->grace_ends_at->greaterThan($now)) {
            return true;
        }

        return false;
    }
}

