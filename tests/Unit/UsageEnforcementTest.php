<?php

namespace Tests\Unit;

use App\Models\Tool;
use App\Models\User;
use App\Services\UsageEnforcementService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_failed_slot_release_does_not_count_toward_daily_quota(): void
    {
        $service = app(UsageEnforcementService::class);
        $user = User::where('email', 'free@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'merge-pdf')->firstOrFail();

        $slot = $service->acquireUsageSlot($user, $tool, '127.0.0.1');
        $this->assertTrue($slot['allowed']);

        // Release slot as failed
        $service->releaseFailedSlot($slot['log']);

        // Completed count must still be 0
        $this->assertSame(0, $service->getTodayUsageCount($user, $tool));
    }

    public function test_admin_can_reset_user_daily_usage(): void
    {
        $service = app(UsageEnforcementService::class);
        $user = User::where('email', 'free@doctix.io')->firstOrFail();
        $admin = User::where('email', 'admin@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'merge-pdf')->firstOrFail();

        for ($i = 1; $i <= 4; $i++) {
            $slot = $service->acquireUsageSlot($user, $tool, '127.0.0.1');
            $service->finalizeSuccess($slot['log']);
        }

        $this->assertFalse($service->checkAllowance($user, $tool)['allowed']);

        // Admin resets usage
        $resetCount = $service->resetUserDailyUsage($user, $tool, $admin);
        $this->assertSame(4, $resetCount);

        // User is allowed again
        $this->assertTrue($service->checkAllowance($user, $tool)['allowed']);
    }
}

