<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\ProcessedFile;
use App\Models\SubscriptionPlan;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Models\ToolUsageLog;
use App\Models\UserProfile;
use App\Services\UsageEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserDashboardController extends Controller
{
    public function __construct(protected UsageEnforcementService $usageService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $usageSummary = $this->usageService->getUserDailySummary($user);
        $activeSubscription = $user->activeSubscription();
        $currentPlan = $user->currentPlan();

        $totalOperationsAllTime = ToolUsageLog::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $recentFiles = ProcessedFile::with('tool')
            ->where('user_id', $user->id)
            ->where('is_deleted', false)
            ->latest()
            ->take(6)
            ->get();

        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        $unreadNotifications = $user->userNotifications()->where('is_read', false)->count();

        return view('dashboard.index', compact(
            'user',
            'usageSummary',
            'activeSubscription',
            'currentPlan',
            'totalOperationsAllTime',
            'recentFiles',
            'recentActivities',
            'unreadNotifications'
        ));
    }

    public function usage(Request $request)
    {
        $user = $request->user();
        $usageSummary = $this->usageService->getUserDailySummary($user);

        // Last 7 days daily count for chart
        $dailyHistory = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now(config('app.timezone', 'UTC'))->subDays($i)->toDateString();
            $dailyHistory[$d] = ToolUsageLog::where('user_id', $user->id)
                ->whereDate('usage_date', $d)
                ->where('status', 'completed')
                ->count();
        }

        return view('dashboard.usage', compact('user', 'usageSummary', 'dailyHistory'));
    }

    public function documents(Request $request)
    {
        $user = $request->user();
        $files = ProcessedFile::with(['tool', 'processingJob'])
            ->where('user_id', $user->id)
            ->where('is_deleted', false)
            ->latest()
            ->paginate(15);

        return view('dashboard.documents', compact('user', 'files'));
    }

    public function activities(Request $request)
    {
        $user = $request->user();
        $activities = ActivityLog::where('user_id', $user->id)->latest()->paginate(20);
        $loginHistories = $user->loginHistories()->take(10)->get();

        return view('dashboard.activities', compact('user', 'activities', 'loginHistories'));
    }

    public function subscription(Request $request)
    {
        $user = $request->user();
        $activeSubscription = $user->activeSubscription();
        $currentPlan = $user->currentPlan();
        $plans = SubscriptionPlan::with('features')->where('is_active', true)->orderBy('sort_order')->get();
        $subscriptionsHistory = $user->subscriptions()->with('plan')->get();
        $payments = $user->payments()->with('plan')->get();
        $invoices = $user->invoices()->get();

        return view('dashboard.subscription', compact(
            'user',
            'activeSubscription',
            'currentPlan',
            'plans',
            'subscriptionsHistory',
            'payments',
            'invoices'
        ));
    }

    public function showInvoice(Request $request, string $invoiceNumber)
    {
        $invoice = Invoice::with(['user', 'subscription.plan', 'payment'])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        if ($invoice->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized access to invoice.');
        }

        return view('dashboard.invoice', compact('invoice'));
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('profile');
        $activeSessions = [];

        if (Schema::hasTable('sessions')) {
            $activeSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get();
        }

        return view('dashboard.profile', compact('user', 'activeSessions'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'company' => 'nullable|string|max:120',
            'job_title' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:40',
            'country' => 'nullable|string|max:80',
            'timezone' => 'nullable|string|max:64',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->name = $validated['name'];
        $user->save();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company' => $validated['company'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
                'timezone' => $validated['timezone'] ?? 'UTC',
                'bio' => $validated['bio'] ?? null,
            ]
        );

        ActivityLog::record($user->id, 'profile.updated', 'Updated account profile details');

        return back()->with('success', 'Your profile information has been saved.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->string('password')),
        ]);

        ActivityLog::record($user->id, 'security.password_changed', 'Changed account password');

        return back()->with('success', 'Password updated successfully.');
    }

    public function toggleTwoFactor(Request $request)
    {
        $user = $request->user();
        $enable = !$user->two_factor_enabled;

        $user->update([
            'two_factor_enabled' => $enable,
            'two_factor_secret' => $enable ? strtoupper(Str::random(16)) : null,
            'two_factor_recovery_codes' => $enable ? json_encode([
                strtoupper(Str::random(10)),
                strtoupper(Str::random(10)),
                strtoupper(Str::random(10)),
                strtoupper(Str::random(10)),
            ]) : null,
        ]);

        ActivityLog::record($user->id, 'security.2fa_toggled', ($enable ? 'Enabled' : 'Disabled') . ' Two-Factor Authentication');

        return back()->with('success', 'Two-Factor Authentication has been ' . ($enable ? 'enabled' : 'disabled') . '.');
    }

    public function revokeSessions(Request $request)
    {
        $user = $request->user();
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return back()->with('success', 'All other active browser sessions have been signed out.');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('home')->with('success', 'Your account and personal data have been permanently deleted.');
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->userNotifications()->paginate(20);
        return view('dashboard.notifications', compact('user', 'notifications'));
    }

    public function markNotificationsRead(Request $request)
    {
        $request->user()->userNotifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function tickets(Request $request)
    {
        $user = $request->user();
        $tickets = $user->supportTickets()->with('replies.user')->get();
        return view('dashboard.tickets', compact('user', 'tickets'));
    }

    public function storeTicket(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'category' => 'required|string|max:50',
            'priority' => 'required|in:low,normal,high,urgent',
            'message' => 'required|string|max:4000',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'message' => $validated['message'],
        ]);

        return back()->with('success', "Support Ticket #{$ticket->ticket_number} created successfully.");
    }

    public function replyTicket(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate(['message' => 'required|string|max:4000']);

        SupportReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'is_staff_reply' => false,
            'message' => $validated['message'],
        ]);

        $ticket->update(['status' => 'customer_reply']);

        return back()->with('success', 'Your reply has been added to the ticket.');
    }
}

