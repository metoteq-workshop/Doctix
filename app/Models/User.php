<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
        'avatar_path',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'unlimited_override',
        'custom_daily_limit',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'unlimited_override' => 'boolean',
            'custom_daily_limit' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class)->latest('logged_in_at');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    public function toolUsageLogs(): HasMany
    {
        return $this->hasMany(ToolUsageLog::class);
    }

    public function processingJobs(): HasMany
    {
        return $this->hasMany(ProcessingJob::class)->latest();
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFileRecord::class)->latest();
    }

    public function processedFiles(): HasMany
    {
        return $this->hasMany(ProcessedFile::class)->latest();
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class)->latest();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function isAdmin(): bool
    {
        if (in_array($this->role, ['admin', 'super_admin'], true)) {
            return true;
        }

        return $this->roles()->whereIn('slug', ['admin', 'super-admin'])->exists();
    }

    public function hasRole(string $roleSlug): bool
    {
        if ($this->role === $roleSlug) {
            return true;
        }

        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('slug', $permissionSlug))
            ->exists();
    }

    /**
     * Retrieve the user's currently active, non-expired subscription (including grace period).
     */
    public function activeSubscription(): ?Subscription
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        return $this->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'grace_period', 'canceled'])
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->where('status', 'grace_period')
                            ->whereNotNull('grace_ends_at')
                            ->where('grace_ends_at', '>', $now);
                    });
            })
            ->whereHas('plan', fn ($q) => $q->where('is_unlimited', true))
            ->first();
    }

    /**
     * Check whether the user currently has unlimited premium entitlement.
     */
    public function hasActivePremiumSubscription(): bool
    {
        if ($this->unlimited_override) {
            return true;
        }

        return $this->activeSubscription() !== null;
    }

    /**
     * Get the user's effective SubscriptionPlan model (falls back to Free Plan).
     */
    public function currentPlan(): ?SubscriptionPlan
    {
        $activeSub = $this->activeSubscription();
        if ($activeSub && $activeSub->plan) {
            return $activeSub->plan;
        }

        return SubscriptionPlan::where('slug', 'free')->first();
    }

    public function maxFileSizeMb(?Tool $tool = null): int
    {
        if ($this->hasActivePremiumSubscription()) {
            $planMax = $this->activeSubscription()?->plan?->max_file_size_mb;
            $toolMax = $tool?->premium_max_file_size_mb ?? (int) SystemSetting::getValue('premium_max_file_size_mb', config('doctix.premium_max_file_size_mb', 250));
            return max((int) $planMax, (int) $toolMax);
        }

        return $tool?->free_max_file_size_mb
            ?? (int) SystemSetting::getValue('free_max_file_size_mb', config('doctix.free_max_file_size_mb', 25));
    }
}
