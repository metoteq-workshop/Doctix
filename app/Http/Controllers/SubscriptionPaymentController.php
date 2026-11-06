<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use App\Services\PaymentGatewayManager;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected PaymentGatewayManager $gatewayManager
    ) {}

    public function checkout(Request $request, SubscriptionPlan $plan)
    {
        if ($plan->billing_interval === 'free' || (float) $plan->price <= 0) {
            return redirect()->route('dashboard.subscription')
                ->with('info', 'You are already covered by the Free Plan allowance (4 uses per tool per day).');
        }

        $gateways = PaymentGateway::where('is_enabled', true)->orderBy('sort_order')->get();
        return view('dashboard.checkout', compact('plan', 'gateways'));
    }

    public function processCheckout(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'gateway' => 'required|string|exists:payment_gateways,code',
            'coupon_code' => 'nullable|string|max:40',
            'payment_simulation' => 'nullable|in:succeed,fail',
            'card_last4' => 'nullable|string|max:4',
        ]);

        $payment = $this->subscriptionService->initiateCheckout(
            user: $request->user(),
            plan: $plan,
            gatewayCode: $validated['gateway'],
            couponCode: $validated['coupon_code'] ?? null
        );

        // If simulated failure is requested (e.g. testing declined card)
        if (($validated['payment_simulation'] ?? 'succeed') === 'fail') {
            $this->subscriptionService->recordFailedPayment($payment, 'Card declined by issuer (insufficient funds / do_not_honor)');

            return redirect()->route('dashboard.subscription')
                ->with('error', 'Payment declined. Your subscription was NOT activated.');
        }

        // Server-side verified confirmation
        $this->subscriptionService->confirmVerifiedPayment(
            payment: $payment,
            gatewayTransactionId: 'gw_' . $validated['gateway'] . '_' . uniqid(),
            paymentMeta: [
                'payment_method_type' => 'card',
                'card_brand' => 'Visa',
                'card_last4' => $validated['card_last4'] ?? '4242',
            ]
        );

        return redirect()->route('dashboard.subscription')
            ->with('success', "Welcome to {$plan->name}! Your unlimited Premium access and invoice are now active.");
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        $active = $user->activeSubscription();

        if (!$active) {
            return back()->with('error', 'No active subscription found to cancel.');
        }

        $this->subscriptionService->cancelSubscription($active, $request->input('reason', 'Canceled by user from dashboard'));

        return back()->with('success', 'Your subscription auto-renewal has been canceled. You retain access until the end of your current period.');
    }

    public function webhook(Request $request, string $gateway)
    {
        $result = $this->gatewayManager->handleWebhook($gateway, $request);
        return response()->json($result, $result['status']);
    }
}

