<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\EmailTemplate;
use App\Models\Faq;
use App\Models\NewsletterSubscriber;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\Testimonial;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\ToolUsageLog;
use App\Models\User;
use App\Services\FileSecurityService;
use App\Services\SubscriptionService;
use App\Services\UsageEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct(
        protected UsageEnforcementService $usageService,
        protected SubscriptionService $subscriptionService,
        protected FileSecurityService $fileSecurity
    ) {}

    /**
     * 1. Admin Dashboard & Analytics Overview
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();

        $activePremiumSubs = Subscription::with('plan')
            ->whereIn('status', ['active', 'grace_period'])
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->get();

        $premiumUsersCount = $activePremiumSubs->pluck('user_id')->unique()->count();
        $freeUsersCount = max(0, $totalUsers - $premiumUsersCount);

        // Calculate MRR & ARR
        $mrr = 0.0;
        foreach ($activePremiumSubs as $sub) {
            if ($sub->plan && $sub->plan->billing_interval === 'annual') {
                $mrr += ((float) $sub->amount) / 12;
            } else {
                $mrr += (float) $sub->amount;
            }
        }
        $arr = $mrr * 12;

        $totalTransactions = Payment::count();
        $successfulPayments = Payment::where('status', 'succeeded')->count();
        $failedPayments = Payment::where('status', 'failed')->count();
        $totalRevenue = (float) Payment::where('status', 'succeeded')->sum('amount');

        $totalToolUsage = ToolUsageLog::where('status', 'completed')->count();
        $todayToolUsage = ToolUsageLog::whereDate('usage_date', $this->usageService->currentUsageDate())
            ->where('status', 'completed')
            ->count();

        $popularTools = Tool::with('category')
            ->orderByDesc('total_usage_count')
            ->take(8)
            ->get();

        $recentUsers = User::latest()->take(6)->get();
        $recentTransactions = Payment::with(['user', 'plan'])->latest()->take(8)->get();

        // 7-day chart series
        $chartLabels = [];
        $chartUsageSeries = [];
        $chartRevenueSeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartUsageSeries[] = ToolUsageLog::whereDate('usage_date', $day)->where('status', 'completed')->count();
            $chartRevenueSeries[] = (float) Payment::whereDate('created_at', $day)->where('status', 'succeeded')->sum('amount');
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'freeUsersCount',
            'premiumUsersCount',
            'mrr',
            'arr',
            'totalTransactions',
            'successfulPayments',
            'failedPayments',
            'totalRevenue',
            'totalToolUsage',
            'todayToolUsage',
            'popularTools',
            'recentUsers',
            'recentTransactions',
            'chartLabels',
            'chartUsageSeries',
            'chartRevenueSeries'
        ));
    }

    public function exportAnalyticsCsv()
    {
        $payments = Payment::with(['user', 'plan'])->latest()->get();
        $csv = "Reference,User Email,Plan,Gateway,Amount,Currency,Status,Date\n";
        foreach ($payments as $p) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%.2f,%s,%s,%s\n",
                $p->transaction_reference,
                $p->user?->email ?? 'N/A',
                str_replace(',', ' ', $p->plan?->name ?? 'N/A'),
                $p->gateway,
                $p->amount,
                $p->currency,
                $p->status,
                $p->created_at?->toIso8601String()
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="doctix-financial-report-' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * 2. User Management & Daily Usage Reset
     */
    public function users(Request $request)
    {
        $query = User::with(['subscriptions.plan']);

        if ($request->filled('q')) {
            $s = trim((string) $request->input('q'));
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        $users = $query->latest()->paginate(15);
        $plans = SubscriptionPlan::where('is_active', true)->get();
        $tools = Tool::where('is_active', true)->orderBy('name')->get();

        return view('admin.users', compact('users', 'plans', 'tools'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,manager,admin',
            'unlimited_override' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'role' => $validated['role'],
            'status' => 'active',
            'unlimited_override' => $request->boolean('unlimited_override'),
        ]);

        AuditLog::logEvent('users', 'user_created', $request->user()->id, User::class, $user->id, [], $user->toArray());

        return back()->with('success', "User {$user->email} created successfully.");
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,manager,admin',
            'status' => 'required|in:active,suspended',
            'unlimited_override' => 'nullable|boolean',
            'custom_daily_limit' => 'nullable|integer|min:1|max:500',
        ]);

        $old = $user->only(['name', 'email', 'role', 'status', 'unlimited_override', 'custom_daily_limit']);
        $user->update([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'status' => $validated['status'],
            'unlimited_override' => $request->boolean('unlimited_override'),
            'custom_daily_limit' => $validated['custom_daily_limit'] ?? null,
        ]);

        AuditLog::logEvent('users', 'user_updated', $request->user()->id, User::class, $user->id, $old, $user->only(array_keys($old)));

        return back()->with('success', "User {$user->name} updated.");
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        AuditLog::logEvent('users', 'user_status_toggled', $request->user()->id, User::class, $user->id, [], ['status' => $newStatus], 'warning');

        return back()->with('success', "User {$user->email} status changed to {$newStatus}.");
    }

    public function deleteUser(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        $email = $user->email;
        $user->delete();

        AuditLog::logEvent('users', 'user_deleted', $request->user()->id, User::class, null, ['email' => $email], [], 'critical');

        return back()->with('success', "User {$email} deleted.");
    }

    public function assignSubscription(Request $request, User $user)
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'duration_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
        $days = (int) ($validated['duration_days'] ?? $plan->interval_days ?: 30);

        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'grace_period'])
            ->update(['status' => 'canceled', 'canceled_at' => now()]);

        $sub = Subscription::create([
            'subscription_uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'payment_gateway' => 'manual',
            'gateway_subscription_id' => 'manual_admin_' . time(),
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'auto_renew' => false,
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
            'grace_ends_at' => now()->addDays($days + 3),
        ]);

        AuditLog::logEvent('billing', 'manual_subscription_assigned', $request->user()->id, Subscription::class, $sub->id, [], [
            'user_id' => $user->id,
            'plan' => $plan->slug,
            'days' => $days,
        ]);

        return back()->with('success', "Assigned {$plan->name} ({$days} days) to {$user->name}.");
    }

    public function resetUserUsage(Request $request, User $user)
    {
        $tool = $request->filled('tool_id') ? Tool::find($request->integer('tool_id')) : null;
        $count = $this->usageService->resetUserDailyUsage($user, $tool, $request->user());

        return back()->with('success', "Reset {$count} daily usage record(s) for {$user->name}.");
    }

    /**
     * 3. Tool Management
     */
    public function tools(Request $request)
    {
        $categories = ToolCategory::orderBy('sort_order')->get();
        $query = Tool::with('category');

        if ($request->filled('q')) {
            $s = trim((string) $request->input('q'));
            $query->where('name', 'like', "%{$s}%");
        }
        if ($request->filled('category_id')) {
            $query->where('tool_category_id', $request->integer('category_id'));
        }

        $tools = $query->orderBy('sort_order')->paginate(25);
        return view('admin.tools', compact('tools', 'categories'));
    }

    public function storeTool(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:140|unique:tools,slug',
            'tool_category_id' => 'required|exists:tool_categories,id',
            'short_description' => 'required|string|max:500',
            'engine_driver' => 'required|in:pdf,image,document,ai,utility',
            'handler_action' => 'required|string|max:80',
            'input_mode' => 'required|in:file,multi_file,text,form_or_file',
            'accepted_formats' => 'nullable|string',
            'output_format' => 'required|string|max:20',
            'daily_free_limit' => 'required|integer|min:1|max:100',
            'free_max_file_size_mb' => 'required|integer|min:1|max:500',
            'premium_max_file_size_mb' => 'required|integer|min:1|max:2000',
            'is_premium_only' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:500',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);
        $formats = array_values(array_filter(array_map('trim', explode(',', (string) ($validated['accepted_formats'] ?? 'pdf')))));

        $tool = Tool::create([
            'tool_category_id' => $validated['tool_category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'short_description' => $validated['short_description'],
            'description' => $validated['short_description'],
            'engine_driver' => $validated['engine_driver'],
            'handler_action' => $validated['handler_action'],
            'input_mode' => $validated['input_mode'],
            'accepted_formats' => $formats,
            'output_format' => strtolower($validated['output_format']),
            'daily_free_limit' => $validated['daily_free_limit'],
            'free_max_file_size_mb' => $validated['free_max_file_size_mb'],
            'premium_max_file_size_mb' => $validated['premium_max_file_size_mb'],
            'is_premium_only' => $request->boolean('is_premium_only'),
            'is_featured' => $request->boolean('is_featured'),
            'is_popular' => $request->boolean('is_popular'),
            'is_active' => true,
            'seo_title' => $validated['seo_title'] ?? ($validated['name'] . ' | Doctix'),
            'seo_description' => $validated['seo_description'] ?? $validated['short_description'],
        ]);

        AuditLog::logEvent('tools', 'tool_created', $request->user()->id, Tool::class, $tool->id, [], $tool->toArray());

        return back()->with('success', "Tool '{$tool->name}' added to the directory.");
    }

    public function updateTool(Request $request, Tool $tool)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'tool_category_id' => 'required|exists:tool_categories,id',
            'short_description' => 'required|string|max:500',
            'daily_free_limit' => 'required|integer|min:1|max:100',
            'free_max_file_size_mb' => 'required|integer|min:1|max:500',
            'premium_max_file_size_mb' => 'required|integer|min:1|max:2000',
            'availability_status' => 'required|in:available,maintenance,coming_soon',
            'is_premium_only' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:500',
        ]);

        $tool->update([
            'name' => $validated['name'],
            'tool_category_id' => $validated['tool_category_id'],
            'short_description' => $validated['short_description'],
            'daily_free_limit' => $validated['daily_free_limit'],
            'free_max_file_size_mb' => $validated['free_max_file_size_mb'],
            'premium_max_file_size_mb' => $validated['premium_max_file_size_mb'],
            'availability_status' => $validated['availability_status'],
            'is_premium_only' => $request->boolean('is_premium_only'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $tool->is_active,
            'is_featured' => $request->boolean('is_featured'),
            'is_popular' => $request->boolean('is_popular'),
            'seo_title' => $validated['seo_title'] ?? $tool->seo_title,
            'seo_description' => $validated['seo_description'] ?? $tool->seo_description,
        ]);

        AuditLog::logEvent('tools', 'tool_updated', $request->user()->id, Tool::class, $tool->id, [], $validated);

        return back()->with('success', "Tool '{$tool->name}' configuration updated.");
    }

    public function toggleToolStatus(Request $request, Tool $tool)
    {
        $tool->update(['is_active' => !$tool->is_active]);
        return back()->with('success', "Tool '{$tool->name}' is now " . ($tool->is_active ? 'Active' : 'Inactive') . '.');
    }

    public function deleteTool(Request $request, Tool $tool)
    {
        $name = $tool->name;
        $tool->delete();
        AuditLog::logEvent('tools', 'tool_deleted', $request->user()->id, Tool::class, null, ['name' => $name], [], 'warning');
        return back()->with('success', "Tool '{$name}' deleted.");
    }

    /**
     * 4. Subscription Plans & Subscriber Management
     */
    public function plans()
    {
        $plans = SubscriptionPlan::with('features')->orderBy('sort_order')->get();
        $subscriptions = Subscription::with(['user', 'plan'])->latest()->paginate(20);
        return view('admin.plans', compact('plans', 'subscriptions'));
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120|unique:subscription_plans,slug',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'billing_interval' => 'required|in:free,monthly,annual',
            'interval_days' => 'required|integer|min:1',
            'is_unlimited' => 'nullable|boolean',
            'daily_tool_limit' => 'required|integer|min:0',
            'max_file_size_mb' => 'required|integer|min:1',
            'grace_period_days' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency']),
            'billing_interval' => $validated['billing_interval'],
            'interval_days' => $validated['interval_days'],
            'is_unlimited' => $request->boolean('is_unlimited'),
            'daily_tool_limit' => $validated['daily_tool_limit'],
            'max_file_size_mb' => $validated['max_file_size_mb'],
            'priority_processing' => $request->boolean('is_unlimited'),
            'access_premium_tools' => $request->boolean('is_unlimited'),
            'access_ai_tools' => true,
            'grace_period_days' => $validated['grace_period_days'] ?? 3,
            'description' => $validated['description'] ?? '',
            'is_active' => true,
        ]);

        AuditLog::logEvent('billing', 'plan_created', $request->user()->id, SubscriptionPlan::class, $plan->id, [], $plan->toArray());

        return back()->with('success', "Subscription plan '{$plan->name}' created.");
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'billing_interval' => 'required|in:free,monthly,annual',
            'interval_days' => 'required|integer|min:1',
            'is_unlimited' => 'nullable|boolean',
            'daily_tool_limit' => 'required|integer|min:0',
            'max_file_size_mb' => 'required|integer|min:1',
            'grace_period_days' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency']),
            'billing_interval' => $validated['billing_interval'],
            'interval_days' => $validated['interval_days'],
            'is_unlimited' => $request->boolean('is_unlimited'),
            'daily_tool_limit' => $validated['daily_tool_limit'],
            'max_file_size_mb' => $validated['max_file_size_mb'],
            'grace_period_days' => $validated['grace_period_days'] ?? $plan->grace_period_days,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $plan->is_active,
            'description' => $validated['description'] ?? $plan->description,
        ]);

        AuditLog::logEvent('billing', 'plan_updated', $request->user()->id, SubscriptionPlan::class, $plan->id, [], $validated);

        return back()->with('success', "Plan '{$plan->name}' updated.");
    }

    public function extendSubscription(Request $request, Subscription $subscription)
    {
        $days = max(1, $request->integer('days', 30));
        $base = ($subscription->ends_at && $subscription->ends_at->isFuture()) ? $subscription->ends_at : now();
        $subscription->update([
            'status' => 'active',
            'ends_at' => $base->copy()->addDays($days),
            'grace_ends_at' => $base->copy()->addDays($days + 3),
        ]);

        return back()->with('success', "Subscription extended by {$days} days.");
    }

    /**
     * 5. Payments & Refunds Management
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['user', 'plan', 'invoice', 'refunds']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->string('gateway'));
        }
        $payments = $query->latest()->paginate(20);
        return view('admin.payments', compact('payments'));
    }

    public function refundPayment(Request $request, Payment $payment)
    {
        if ($payment->status !== 'succeeded') {
            return back()->with('error', 'Only succeeded payments can be refunded.');
        }

        $reason = (string) $request->input('reason', 'Admin initiated refund');
        $this->subscriptionService->processRefund($payment, (float) $payment->amount, $reason, $request->user());

        return back()->with('success', "Payment {$payment->transaction_reference} refunded and associated subscription revoked.");
    }

    /**
     * 6. CMS, Blog, FAQs, Testimonials & Email Templates
     */
    public function cms()
    {
        $blogCategories = BlogCategory::all();
        $blogPosts = BlogPost::with('category')->latest()->get();
        $faqs = Faq::orderBy('sort_order')->get();
        $testimonials = Testimonial::orderBy('sort_order')->get();
        $emailTemplates = EmailTemplate::all();
        $subscribersCount = NewsletterSubscriber::count();

        return view('admin.cms', compact(
            'blogCategories',
            'blogPosts',
            'faqs',
            'testimonials',
            'emailTemplates',
            'subscribersCount'
        ));
    }

    public function storeBlogPost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
        ]);

        BlogPost::create([
            'blog_category_id' => $validated['blog_category_id'],
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::lower(Str::random(4)),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'is_published' => true,
            'published_at' => now(),
            'seo_title' => $validated['title'] . ' | Doctix Blog',
            'seo_description' => $validated['excerpt'],
        ]);

        return back()->with('success', 'Blog article published.');
    }

    public function deleteBlogPost(BlogPost $post)
    {
        $post->delete();
        return back()->with('success', 'Blog post deleted.');
    }

    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:60',
            'question' => 'required|string|max:300',
            'answer' => 'required|string|max:2000',
        ]);

        Faq::create([
            'category' => $validated['category'],
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'is_featured' => true,
            'is_active' => true,
        ]);

        return back()->with('success', 'FAQ item added.');
    }

    public function deleteFaq(Faq $faq)
    {
        $faq->delete();
        return back()->with('success', 'FAQ item removed.');
    }

    public function storeTestimonial(Request $request)
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:120',
            'author_role' => 'required|string|max:120',
            'company' => 'required|string|max:120',
            'quote' => 'required|string|max:1000',
        ]);

        Testimonial::create([
            'author_name' => $validated['author_name'],
            'author_role' => $validated['author_role'],
            'company' => $validated['company'],
            'quote' => $validated['quote'],
            'rating' => 5,
            'avatar_initials' => strtoupper(substr($validated['author_name'], 0, 2)),
            'is_featured' => true,
            'is_active' => true,
        ]);

        return back()->with('success', 'Customer testimonial published.');
    }

    public function updateEmailTemplate(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:250',
            'body_html' => 'required|string',
        ]);

        $template->update($validated);
        return back()->with('success', "Email template '{$template->name}' updated.");
    }

    /**
     * 7. Support Tickets Management
     */
    public function tickets()
    {
        $tickets = SupportTicket::with(['user', 'replies.user'])->latest()->paginate(20);
        return view('admin.tickets', compact('tickets'));
    }

    public function replyTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate(['message' => 'required|string|max:4000']);

        SupportReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'is_staff_reply' => true,
            'message' => $validated['message'],
        ]);

        $ticket->update(['status' => 'answered']);

        return back()->with('success', "Reply sent to Ticket #{$ticket->ticket_number}.");
    }

    public function closeTicket(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);
        return back()->with('success', "Ticket #{$ticket->ticket_number} closed.");
    }

    /**
     * 8. System Settings, Payment Gateways & AI Credentials
     */
    public function settings()
    {
        $settings = SystemSetting::all()->pluck('setting_value', 'setting_key');
        $gateways = PaymentGateway::orderBy('sort_order')->get();
        return view('admin.settings', compact('settings', 'gateways'));
    }

    public function updateSettings(Request $request)
    {
        $fields = [
            'site_name' => ['general', 'string', true],
            'site_tagline' => ['general', 'string', true],
            'support_email' => ['general', 'string', true],
            'hero_headline' => ['general', 'string', true],
            'hero_subheadline' => ['general', 'string', true],
            'daily_free_limit' => ['limits', 'integer', true],
            'allow_guest_tools' => ['limits', 'boolean', true],
            'guest_daily_limit' => ['limits', 'integer', true],
            'free_max_file_size_mb' => ['limits', 'integer', true],
            'premium_max_file_size_mb' => ['limits', 'integer', true],
            'temp_file_retention_hours' => ['storage', 'integer', false],
            'ai_provider' => ['ai', 'string', false],
            'meta_title' => ['seo', 'string', true],
            'meta_description' => ['seo', 'string', true],
        ];

        foreach ($fields as $key => [$group, $type, $isPublic]) {
            if ($request->has($key)) {
                SystemSetting::setValue($key, $request->input($key), $group, $type, $isPublic);
            }
        }

        if ($request->filled('openai_api_key')) {
            SystemSetting::setValue('openai_api_key', $request->string('openai_api_key'), 'ai', 'encrypted', false);
        }

        AuditLog::logEvent('settings', 'system_settings_updated', $request->user()->id);

        return back()->with('success', 'System configuration, quotas, and SEO settings saved.');
    }

    public function updateGateway(Request $request, PaymentGateway $gateway)
    {
        $gateway->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'is_sandbox' => $request->boolean('is_sandbox'),
            'public_key' => $request->input('public_key', $gateway->public_key),
        ]);

        if ($request->filled('secret_key')) {
            $gateway->update(['secret_key' => encrypt((string) $request->input('secret_key'))]);
        }

        AuditLog::logEvent('billing', 'gateway_updated', $request->user()->id, PaymentGateway::class, $gateway->id);

        return back()->with('success', "{$gateway->name} gateway configuration updated.");
    }

    /**
     * 9. Activity, Security & Audit Logs + System Health
     */
    public function logs()
    {
        $auditLogs = AuditLog::with('actor')->latest()->paginate(25);
        $activityLogs = ActivityLog::with('user')->latest()->take(30)->get();

        $systemHealth = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone', 'UTC'),
            'db_driver' => DB::connection()->getDriverName(),
            'storage_writable' => is_writable(storage_path('app')),
            'gd_loaded' => extension_loaded('gd'),
            'openssl_loaded' => extension_loaded('openssl'),
        ];

        return view('admin.logs', compact('auditLogs', 'activityLogs', 'systemHealth'));
    }

    public function runMaintenance()
    {
        $stats = $this->fileSecurity->cleanupExpiredFiles();
        return back()->with('success', "Storage cleanup completed: {$stats['uploads_deleted']} expired uploads and {$stats['processed_deleted']} processed files purged.");
    }
}

