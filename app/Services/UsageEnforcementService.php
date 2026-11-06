<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\Tool;
use App\Models\ToolUsageLog;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsageEnforcementService
{
    /**
     * Get the current calendar date string in the application's configured timezone.
     */
    public function currentUsageDate(): string
    {
        return Carbon::now(config('app.timezone', 'UTC'))->toDateString();
    }

    /**
     * Count completed or actively reserved uses for a specific tool today.
     */
    public function getTodayUsageCount(?User $user, Tool $tool, ?string $ipAddress = null, bool $includeReserved = false): int
    {
        $today = $this->currentUsageDate();
        $statuses = $includeReserved ? ['completed', 'reserved'] : ['completed'];

        $query = ToolUsageLog::query()
            ->where('tool_id', $tool->id)
            ->whereDate('usage_date', $today)
            ->whereIn('status', $statuses);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereNull('user_id')
                ->where('ip_address', $ipAddress ?: '127.0.0.1');
        }

        return $query->count();
    }

    /**
     * Check whether the user (or guest) can use the given tool right now.
     *
     * @return array{allowed: bool, reason: string|null, used_today: int, daily_limit: int|null, is_unlimited: bool, plan_tier: string, upgrade_required: bool}
     */
    public function checkAllowance(?User $user, Tool $tool, ?string $ipAddress = null): array
    {
        if (!$tool->is_active || $tool->availability_status !== 'available') {
            return [
                'allowed' => false,
                'reason' => "The {$tool->name} tool is currently unavailable or undergoing maintenance.",
                'used_today' => 0,
                'daily_limit' => 0,
                'is_unlimited' => false,
                'plan_tier' => 'none',
                'upgrade_required' => false,
            ];
        }

        // Authenticated user check
        if ($user) {
            if ($user->status !== 'active') {
                return [
                    'allowed' => false,
                    'reason' => 'Your account has been suspended. Please contact support.',
                    'used_today' => 0,
                    'daily_limit' => 0,
                    'is_unlimited' => false,
                    'plan_tier' => 'suspended',
                    'upgrade_required' => false,
                ];
            }

            if ($user->hasActivePremiumSubscription()) {
                $usedToday = $this->getTodayUsageCount($user, $tool, $ipAddress, false);
                return [
                    'allowed' => true,
                    'reason' => null,
                    'used_today' => $usedToday,
                    'daily_limit' => null,
                    'is_unlimited' => true,
                    'plan_tier' => $user->unlimited_override ? 'admin_override' : 'premium',
                    'upgrade_required' => false,
                ];
            }

            if ($tool->is_premium_only) {
                return [
                    'allowed' => false,
                    'reason' => "{$tool->name} is an exclusive Premium tool. Upgrade your plan to unlock instant access.",
                    'used_today' => 0,
                    'daily_limit' => 0,
                    'is_unlimited' => false,
                    'plan_tier' => 'free',
                    'upgrade_required' => true,
                ];
            }

            $dailyLimit = $tool->effectiveDailyFreeLimit($user);
            $usedToday = $this->getTodayUsageCount($user, $tool, $ipAddress, true);

            if ($usedToday >= $dailyLimit) {
                return [
                    'allowed' => false,
                    'reason' => "You have reached your daily free limit of {$dailyLimit} uses for {$tool->name}. Upgrade to Premium for unlimited access, or try another tool!",
                    'used_today' => $usedToday,
                    'daily_limit' => $dailyLimit,
                    'is_unlimited' => false,
                    'plan_tier' => 'free',
                    'upgrade_required' => true,
                ];
            }

            return [
                'allowed' => true,
                'reason' => null,
                'used_today' => $usedToday,
                'daily_limit' => $dailyLimit,
                'is_unlimited' => false,
                'plan_tier' => 'free',
                'upgrade_required' => false,
            ];
        }

        // Guest user check
        $allowGuests = (bool) SystemSetting::getValue('allow_guest_tools', config('doctix.allow_guest_tools', true));
        if (!$allowGuests || !$tool->allow_guest || $tool->is_premium_only) {
            return [
                'allowed' => false,
                'reason' => 'Please create a free account or sign in to use this tool (4 free uses per tool every day).',
                'used_today' => 0,
                'daily_limit' => 0,
                'is_unlimited' => false,
                'plan_tier' => 'guest',
                'upgrade_required' => true,
            ];
        }

        $guestLimit = (int) SystemSetting::getValue('guest_daily_limit', config('doctix.guest_daily_limit', 2));
        $usedToday = $this->getTodayUsageCount(null, $tool, $ipAddress, true);

        if ($usedToday >= $guestLimit) {
            return [
                'allowed' => false,
                'reason' => "Guest limit of {$guestLimit} uses reached for {$tool->name}. Register a free account to unlock 4 uses per tool per day, or upgrade to Premium!",
                'used_today' => $usedToday,
                'daily_limit' => $guestLimit,
                'is_unlimited' => false,
                'plan_tier' => 'guest',
                'upgrade_required' => true,
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'used_today' => $usedToday,
            'daily_limit' => $guestLimit,
            'is_unlimited' => false,
            'plan_tier' => 'guest',
            'upgrade_required' => false,
        ];
    }

    /**
     * Atomically verify quota with pessimistic row locking and reserve a usage slot.
     * Prevents concurrent requests from bypassing the 4-uses-per-tool-per-day limit.
     *
     * @return array{allowed: bool, log: ToolUsageLog|null, check: array}
     */
    public function acquireUsageSlot(
        ?User $user,
        Tool $tool,
        ?string $ipAddress = null,
        ?string $idempotencyKey = null,
        ?string $sessionId = null
    ): array {
        return DB::transaction(function () use ($user, $tool, $ipAddress, $idempotencyKey, $sessionId) {
            // Acquire pessimistic row lock on User (or Tool for guests) to serialize concurrent attempts
            if ($user) {
                User::where('id', $user->id)->lockForUpdate()->first();
            } else {
                Tool::where('id', $tool->id)->lockForUpdate()->first();
            }

            // Idempotency check: if key already exists, return existing record
            if ($idempotencyKey) {
                $existing = ToolUsageLog::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
                if ($existing) {
                    $check = $this->checkAllowance($user, $tool, $ipAddress);
                    return [
                        'allowed' => in_array($existing->status, ['reserved', 'completed'], true),
                        'log' => $existing,
                        'check' => $check,
                        'idempotent_replay' => true,
                    ];
                }
            }

            $check = $this->checkAllowance($user, $tool, $ipAddress);
            if (!$check['allowed']) {
                return [
                    'allowed' => false,
                    'log' => null,
                    'check' => $check,
                    'idempotent_replay' => false,
                ];
            }

            $log = ToolUsageLog::create([
                'user_id' => $user?->id,
                'tool_id' => $tool->id,
                'usage_date' => $this->currentUsageDate(),
                'ip_address' => $ipAddress ?: '127.0.0.1',
                'session_id' => $sessionId,
                'idempotency_key' => $idempotencyKey ?: (string) Str::uuid(),
                'plan_tier' => $check['plan_tier'],
                'status' => 'reserved',
            ]);

            return [
                'allowed' => true,
                'log' => $log,
                'check' => $check,
                'idempotent_replay' => false,
            ];
        });
    }

    /**
     * Commit a reserved usage slot ONLY when the tool operation succeeds.
     */
    public function finalizeSuccess(
        ToolUsageLog $log,
        ?int $processingJobId = null,
        int $inputBytes = 0,
        int $outputBytes = 0,
        int $processingTimeMs = 0
    ): ToolUsageLog {
        return DB::transaction(function () use ($log, $processingJobId, $inputBytes, $outputBytes, $processingTimeMs) {
            $log->update([
                'processing_job_id' => $processingJobId,
                'status' => 'completed',
                'input_bytes' => $inputBytes,
                'output_bytes' => $outputBytes,
                'processing_time_ms' => $processingTimeMs,
            ]);

            Tool::where('id', $log->tool_id)->increment('total_usage_count');

            // If user just hit the daily limit for this tool, send a helpful notification
            if ($log->user_id && $log->plan_tier === 'free') {
                $user = User::find($log->user_id);
                $tool = Tool::find($log->tool_id);
                if ($user && $tool) {
                    $used = $this->getTodayUsageCount($user, $tool, null, false);
                    $limit = $tool->effectiveDailyFreeLimit($user);
                    if ($used === $limit) {
                        UserNotification::create([
                            'user_id' => $user->id,
                            'title' => "Daily Limit Reached for {$tool->name}",
                            'message' => "You have used all {$limit} free daily operations for {$tool->name} today. Your other tools still have their own 4 free uses, or upgrade to Premium for unlimited access!",
                            'type' => 'warning',
                            'action_url' => '/pricing',
                        ]);
                    }
                }
            }

            return $log->fresh();
        });
    }

    /**
     * Release a reserved slot if processing fails so failed operations do NOT consume free quota.
     */
    public function releaseFailedSlot(ToolUsageLog $log): void
    {
        $log->update(['status' => 'failed']);
    }

    /**
     * Reset a user's daily usage for a specific tool or all tools today (Admin action).
     */
    public function resetUserDailyUsage(User $user, ?Tool $tool = null, ?User $admin = null): int
    {
        $today = $this->currentUsageDate();
        $query = ToolUsageLog::where('user_id', $user->id)
            ->whereDate('usage_date', $today)
            ->whereIn('status', ['completed', 'reserved']);

        if ($tool) {
            $query->where('tool_id', $tool->id);
        }

        $affected = $query->update(['status' => 'reset_by_admin']);

        AuditLog::logEvent(
            module: 'usage',
            action: 'reset_daily_usage',
            actorId: $admin?->id,
            targetType: User::class,
            targetId: $user->id,
            newValues: [
                'tool_id' => $tool?->id,
                'tool_name' => $tool?->name ?? 'All Tools',
                'records_reset' => $affected,
                'date' => $today,
            ],
            severity: 'warning'
        );

        return $affected;
    }

    /**
     * Build a structured usage breakdown across active tools for the User Dashboard.
     */
    public function getUserDailySummary(User $user): array
    {
        $today = $this->currentUsageDate();
        $isUnlimited = $user->hasActivePremiumSubscription();

        $countsByTool = ToolUsageLog::query()
            ->select('tool_id', DB::raw('COUNT(*) as total'))
            ->where('user_id', $user->id)
            ->whereDate('usage_date', $today)
            ->where('status', 'completed')
            ->groupBy('tool_id')
            ->pluck('total', 'tool_id');

        $tools = Tool::with('category')
            ->where('is_active', true)
            ->orderByDesc('is_popular')
            ->orderBy('sort_order')
            ->get();

        $rows = [];
        foreach ($tools as $tool) {
            $used = (int) ($countsByTool[$tool->id] ?? 0);
            $limit = $tool->effectiveDailyFreeLimit($user);
            $remaining = $isUnlimited ? null : max(0, $limit - $used);
            $percentage = $isUnlimited ? min(100, $used * 10) : min(100, (int) round(($used / max(1, $limit)) * 100));

            $rows[] = [
                'tool' => $tool,
                'used_today' => $used,
                'daily_limit' => $isUnlimited ? 'Unlimited' : $limit,
                'remaining' => $isUnlimited ? 'Unlimited' : $remaining,
                'percentage' => $percentage,
                'status' => ($isUnlimited || $used < $limit) ? 'Available' : 'Limit Reached',
                'is_limit_reached' => !$isUnlimited && $used >= $limit,
            ];
        }

        // Sort so tools used today appear first
        usort($rows, fn ($a, $b) => $b['used_today'] <=> $a['used_today']);

        return [
            'date' => $today,
            'timezone' => config('app.timezone', 'UTC'),
            'is_unlimited' => $isUnlimited,
            'total_operations_today' => array_sum($countsByTool->all()),
            'tools_used_count_today' => $countsByTool->count(),
            'rows' => $rows,
        ];
    }
}

