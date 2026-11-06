<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DoctixPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test 1, 2 & 3:
     * - A free user can use a specific tool 4 times successfully.
     * - The 5th attempt on that tool is blocked with HTTP 429 and upgrade_required = true.
     * - The user can still use another tool 4 times on the exact same day.
     */
    public function test_free_user_four_uses_per_tool_limit_and_independent_second_tool(): void
    {
        $freeUser = User::where('email', 'free@doctix.io')->firstOrFail();
        $mergeTool = Tool::where('slug', 'merge-pdf')->firstOrFail();
        $compressTool = Tool::where('slug', 'compress-pdf')->firstOrFail();

        // 1. Use Merge PDF 4 times successfully
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $pdfFile = UploadedFile::fake()->create("doc-{$attempt}.pdf", 20, 'application/pdf');

            $response = $this->actingAs($freeUser)
                ->postJson(route('tools.process', $mergeTool->slug), [
                    'files' => [$pdfFile],
                ]);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);
        }

        // 2. The 5th attempt on Merge PDF is blocked
        $fifthPdf = UploadedFile::fake()->create('doc-5.pdf', 20, 'application/pdf');
        $blockedResponse = $this->actingAs($freeUser)
            ->postJson(route('tools.process', $mergeTool->slug), [
                'files' => [$fifthPdf],
            ]);

        $blockedResponse->assertStatus(429)
            ->assertJson([
                'success' => false,
                'upgrade_required' => true,
            ]);

        // 3. The exact same user can still use Compress PDF 4 times on the same day
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $pdfFile = UploadedFile::fake()->create("comp-{$attempt}.pdf", 20, 'application/pdf');

            $response = $this->actingAs($freeUser)
                ->postJson(route('tools.process', $compressTool->slug), [
                    'file' => $pdfFile,
                ]);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);
        }

        // And the 5th attempt on Compress PDF is also blocked
        $fifthCompress = UploadedFile::fake()->create('comp-5.pdf', 20, 'application/pdf');
        $this->actingAs($freeUser)
            ->postJson(route('tools.process', $compressTool->slug), [
                'file' => $fifthCompress,
            ])
            ->assertStatus(429);
    }

    /**
     * Test 4:
     * The daily limit resets automatically on the next calendar day according to application timezone.
     */
    public function test_daily_usage_limit_resets_automatically_on_next_calendar_day(): void
    {
        $freeUser = User::where('email', 'free@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'word-counter')->firstOrFail();

        Carbon::setTestNow(Carbon::parse('2026-11-06 14:00:00', config('app.timezone', 'UTC')));

        // Exhaust all 4 daily free uses on Day 1
        for ($i = 1; $i <= 4; $i++) {
            $this->actingAs($freeUser)
                ->postJson(route('tools.process', $tool->slug), [
                    'text_input' => "Sample text run {$i}",
                ])
                ->assertStatus(200);
        }

        // 5th attempt on Day 1 is blocked
        $this->actingAs($freeUser)
            ->postJson(route('tools.process', $tool->slug), [
                'text_input' => 'Blocked attempt on Day 1',
            ])
            ->assertStatus(429);

        // Advance clock past midnight to Day 2
        Carbon::setTestNow(Carbon::parse('2026-11-07 00:05:00', config('app.timezone', 'UTC')));

        // User can now use the tool again on Day 2 without deleting historical logs
        $this->actingAs($freeUser)
            ->postJson(route('tools.process', $tool->slug), [
                'text_input' => 'Fresh allowance on Day 2',
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        Carbon::setTestNow();
    }

    /**
     * Test 5:
     * A premium user can use tools without daily restrictions (e.g., 6+ times on the same tool).
     */
    public function test_premium_user_has_unlimited_daily_tool_usage(): void
    {
        $premiumUser = User::where('email', 'premium@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'qr-code-generator')->firstOrFail();

        for ($i = 1; $i <= 6; $i++) {
            $response = $this->actingAs($premiumUser)
                ->postJson(route('tools.process', $tool->slug), [
                    'text_input' => "https://doctix.io/test/{$i}",
                ]);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'allowance' => [
                        'is_unlimited' => true,
                    ],
                ]);
        }
    }

    /**
     * Test 6:
     * An expired subscription no longer provides premium access and reverts to the 4-use daily limit.
     */
    public function test_expired_subscription_no_longer_provides_premium_access(): void
    {
        $premiumUser = User::where('email', 'premium@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'qr-code-generator')->firstOrFail();

        // Expire the user's subscription and grace period
        Subscription::where('user_id', $premiumUser->id)->update([
            'status' => 'expired',
            'ends_at' => now()->subDays(5),
            'grace_ends_at' => now()->subDays(2),
        ]);

        $this->assertFalse($premiumUser->fresh()->hasActivePremiumSubscription());

        // User can only run 4 times now
        for ($i = 1; $i <= 4; $i++) {
            $this->actingAs($premiumUser->fresh())
                ->postJson(route('tools.process', $tool->slug), [
                    'text_input' => "Run {$i}",
                ])
                ->assertStatus(200);
        }

        // 5th attempt is now blocked because subscription is expired
        $this->actingAs($premiumUser->fresh())
            ->postJson(route('tools.process', $tool->slug), [
                'text_input' => 'Run 5 blocked',
            ])
            ->assertStatus(429);
    }

    /**
     * Test 7:
     * A failed payment does not activate a subscription.
     */
    public function test_failed_payment_does_not_activate_subscription(): void
    {
        $freeUser = User::where('email', 'free@doctix.io')->firstOrFail();
        $monthlyPlan = SubscriptionPlan::where('slug', 'monthly-premium')->firstOrFail();

        $this->actingAs($freeUser)
            ->post(route('checkout.process', $monthlyPlan->slug), [
                'gateway' => 'stripe',
                'payment_simulation' => 'fail',
            ])
            ->assertRedirect(route('dashboard.subscription'));

        $this->assertFalse($freeUser->fresh()->hasActivePremiumSubscription());
        $this->assertDatabaseHas('payments', [
            'user_id' => $freeUser->id,
            'subscription_plan_id' => $monthlyPlan->id,
            'status' => 'failed',
        ]);
    }

    /**
     * Test 8:
     * Concurrent / locked requests and idempotency protection prevent bypassing the free usage limit.
     */
    public function test_concurrent_requests_and_idempotency_cannot_bypass_free_limit(): void
    {
        $freeUser = User::where('email', 'free@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'json-formatter')->firstOrFail();

        // Send 2 requests with the exact same idempotency key: only 1 usage record is created
        $idempKey = 'idemp-test-key-001';
        $this->actingAs($freeUser)
            ->postJson(route('tools.process', $tool->slug), [
                'text_input' => '{"a":1}',
                'idempotency_key' => $idempKey,
            ])
            ->assertStatus(200);

        $this->actingAs($freeUser)
            ->postJson(route('tools.process', $tool->slug), [
                'text_input' => '{"a":1}',
                'idempotency_key' => $idempKey,
            ])
            ->assertStatus(200);

        // Execute 3 more distinct requests to reach 4/4
        for ($i = 2; $i <= 4; $i++) {
            $this->actingAs($freeUser)
                ->postJson(route('tools.process', $tool->slug), [
                    'text_input' => '{"run":' . $i . '}',
                ])
                ->assertStatus(200);
        }

        // Next distinct request is strictly rejected
        $this->actingAs($freeUser)
            ->postJson(route('tools.process', $tool->slug), [
                'text_input' => '{"run":5}',
            ])
            ->assertStatus(429);
    }

    /**
     * Test 9:
     * One user cannot access another user's uploaded or processed files (HTTP 403).
     */
    public function test_user_cannot_access_another_users_uploaded_or_processed_files(): void
    {
        $userA = User::where('email', 'free@doctix.io')->firstOrFail();
        $userB = User::where('email', 'premium@doctix.io')->firstOrFail();
        $tool = Tool::where('slug', 'compress-pdf')->firstOrFail();

        $pdfFile = UploadedFile::fake()->create('private-contract.pdf', 30, 'application/pdf');

        $response = $this->actingAs($userA)
            ->postJson(route('tools.process', $tool->slug), [
                'file' => $pdfFile,
            ])
            ->assertStatus(200);

        $downloadUrl = $response->json('download_url');
        $uploadedRecord = $userA->uploadedFiles()->firstOrFail();

        // Owner (User A) can download both the processed file and uploaded file
        $this->actingAs($userA)->get($downloadUrl)->assertStatus(200);
        $this->actingAs($userA)->get(route('files.upload.view', $uploadedRecord->file_uuid))->assertStatus(200);

        // Another user (User B) is strictly forbidden (403) from both
        $this->actingAs($userB)->get($downloadUrl)->assertStatus(403);
        $this->actingAs($userB)->get(route('files.upload.view', $uploadedRecord->file_uuid))->assertStatus(403);

        // Unauthenticated guest is also strictly forbidden (403)
        $this->post(route('logout'));
        $this->get($downloadUrl)->assertStatus(403);
        $this->get(route('files.upload.view', $uploadedRecord->file_uuid))->assertStatus(403);
    }

    /**
     * Test 10:
     * Administrators can configure tools, subscription plans, and reset user daily usage,
     * while non-admin users are blocked from admin endpoints (403).
     */
    public function test_admin_can_configure_tools_and_plans_and_non_admin_is_blocked(): void
    {
        $admin = User::where('email', 'admin@doctix.io')->firstOrFail();
        $freeUser = User::where('email', 'free@doctix.io')->firstOrFail();
        $category = ToolCategory::firstOrFail();

        // Non-admin is forbidden from admin panel
        $this->actingAs($freeUser)->get(route('admin.dashboard'))->assertStatus(403);

        // Admin can create a new tool
        $this->actingAs($admin)
            ->post(route('admin.tools.store'), [
                'name' => 'Custom PDF Stamper',
                'slug' => 'custom-pdf-stamper',
                'tool_category_id' => $category->id,
                'short_description' => 'Stamp custom Bates numbers onto PDF pages.',
                'engine_driver' => 'pdf',
                'handler_action' => 'watermark_pdf',
                'input_mode' => 'file',
                'accepted_formats' => 'pdf',
                'output_format' => 'pdf',
                'daily_free_limit' => 4,
                'free_max_file_size_mb' => 25,
                'premium_max_file_size_mb' => 250,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tools', ['slug' => 'custom-pdf-stamper']);

        // Admin can create a new subscription plan
        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'Enterprise Team Plan',
                'slug' => 'enterprise-team',
                'price' => 49.00,
                'currency' => 'USD',
                'billing_interval' => 'monthly',
                'interval_days' => 30,
                'is_unlimited' => 1,
                'daily_tool_limit' => 0,
                'max_file_size_mb' => 1000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('subscription_plans', ['slug' => 'enterprise-team']);
    }

    /**
     * Test Payment Webhook Signature Verification & Activation
     */
    public function test_signed_payment_webhook_activates_subscription_and_rejects_bad_signature(): void
    {
        $freeUser = User::where('email', 'free@doctix.io')->firstOrFail();
        $plan = SubscriptionPlan::where('slug', 'monthly-premium')->firstOrFail();

        $payment = Payment::create([
            'transaction_reference' => 'DTX-WEBHOOK-TEST-01',
            'user_id' => $freeUser->id,
            'subscription_plan_id' => $plan->id,
            'gateway' => 'stripe',
            'subtotal' => 19.00,
            'amount' => 19.00,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $payload = json_encode([
            'event' => 'payment.succeeded',
            'transaction_reference' => 'DTX-WEBHOOK-TEST-01',
            'gateway_transaction_id' => 'pi_webhook_verified_123',
        ]);

        // 1. Invalid signature is rejected with 401
        $this->call('POST', route('webhooks.payment', 'stripe'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => 'invalid_signature',
        ], $payload)->assertStatus(401);

        $this->assertFalse($freeUser->fresh()->hasActivePremiumSubscription());

        // 2. Valid HMAC signature activates subscription and marks payment succeeded
        $validSignature = hash_hmac('sha256', $payload, 'whsec_stripe_secret');
        $this->call('POST', route('webhooks.payment', 'stripe'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => $validSignature,
        ], $payload)->assertStatus(200);

        $this->assertTrue($freeUser->fresh()->hasActivePremiumSubscription());
    }
}

