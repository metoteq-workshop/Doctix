<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Initiate a checkout transaction for a user and subscription plan.
     * Note: Subscription is NOT activated until server-side payment verification succeeds.
     */
    public function initiateCheckout(
        User $user,
        SubscriptionPlan $plan,
        string $gatewayCode = 'stripe',
        ?string $couponCode = null
    ): Payment {
        $gateway = PaymentGateway::where('code', $gatewayCode)->where('is_enabled', true)->first();
        if (!$gateway) {
            throw new \InvalidArgumentException("Selected payment gateway ({$gatewayCode}) is currently disabled or unavailable.");
        }

        $subtotal = (float) $plan->price;
        $discountAmount = 0.0;
        $coupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper(trim($couponCode)))
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->first();

            if ($coupon && ($coupon->max_redemptions === null || $coupon->redemptions_count < $coupon->max_redemptions)) {
                $discountAmount = $coupon->discount_type === 'percentage'
                    ? round($subtotal * ((float) $coupon->discount_value / 100), 2)
                    : min($subtotal, (float) $coupon->discount_value);
            }
        }

        $netAmount = max(0, $subtotal - $discountAmount);
        $taxRate = (float) config('doctix.tax_percentage', 0.0);
        $taxAmount = round($netAmount * ($taxRate / 100), 2);
        $totalAmount = round($netAmount + $taxAmount, 2);

        $reference = 'DTX-' . strtoupper(Str::random(10)) . '-' . time();

        $payment = Payment::create([
            'transaction_reference' => $reference,
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'gateway' => $gatewayCode,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'amount' => $totalAmount,
            'currency' => $plan->currency ?: 'USD',
            'status' => 'pending',
            'webhook_verified' => false,
            'gateway_payload' => [
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'initiated_at' => now()->toIso8601String(),
            ],
        ]);

        return $payment;
    }

    /**
     * Activate or renew a user's subscription ONLY upon verified server-side payment confirmation.
     */
    public function confirmVerifiedPayment(
        Payment $payment,
        string $gatewayTransactionId,
        array $paymentMeta = []
    ): Subscription {
        return DB::transaction(function () use ($payment, $gatewayTransactionId, $paymentMeta) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status === 'succeeded' && $lockedPayment->subscription_id) {
                return Subscription::findOrFail($lockedPayment->subscription_id);
            }

            $user = User::findOrFail($lockedPayment->user_id);
            $plan = SubscriptionPlan::findOrFail($lockedPayment->subscription_plan_id);

            // Cancel any previous active subscriptions for this user before activating the new one
            Subscription::where('user_id', $user->id)
                ->whereIn('status', ['active', 'grace_period'])
                ->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'cancel_reason' => 'Replaced by upgraded/renewed subscription',
                ]);

            $startsAt = now();
            $endsAt = now()->addDays(max(1, $plan->interval_days ?: 30));
            $graceEndsAt = $endsAt->copy()->addDays($plan->grace_period_days ?: 3);

            $subscription = Subscription::create([
                'subscription_uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'payment_gateway' => $lockedPayment->gateway,
                'gateway_subscription_id' => $paymentMeta['gateway_subscription_id'] ?? ('sub_' . $gatewayTransactionId),
                'amount' => $lockedPayment->amount,
                'currency' => $lockedPayment->currency,
                'auto_renew' => true,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'grace_ends_at' => $graceEndsAt,
            ]);

            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'subscription_plan_id' => $plan->id,
                'quantity' => 1,
                'unit_price' => $plan->price,
            ]);

            $lockedPayment->update([
                'subscription_id' => $subscription->id,
                'gateway_transaction_id' => $gatewayTransactionId,
                'status' => 'succeeded',
                'payment_method_type' => $paymentMeta['payment_method_type'] ?? 'card',
                'card_brand' => $paymentMeta['card_brand'] ?? 'Visa',
                'card_last4' => $paymentMeta['card_last4'] ?? '4242',
                'webhook_verified' => true,
                'paid_at' => now(),
            ]);

            // Record coupon redemption if used
            $couponId = $lockedPayment->gateway_payload['coupon_id'] ?? null;
            if ($couponId) {
                $coupon = Coupon::find($couponId);
                if ($coupon) {
                    $coupon->increment('redemptions_count');
                    CouponRedemption::create([
                        'coupon_id' => $coupon->id,
                        'user_id' => $user->id,
                        'payment_id' => $lockedPayment->id,
                        'discount_applied' => $lockedPayment->discount_amount,
                    ]);
                }
            }

            // Generate Invoice
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);
            Invoice::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $lockedPayment->id,
                'subtotal' => $lockedPayment->subtotal,
                'discount' => $lockedPayment->discount_amount,
                'tax' => $lockedPayment->tax_amount,
                'total' => $lockedPayment->amount,
                'currency' => $lockedPayment->currency,
                'status' => 'paid',
                'billing_name' => $user->name,
                'billing_email' => $user->email,
                'issued_at' => now(),
                'paid_at' => now(),
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'title' => "{$plan->name} Activated!",
                'message' => "Your payment ({$lockedPayment->transaction_reference}) has been verified. You now have unlimited daily access to all tools!",
                'type' => 'success',
                'action_url' => '/dashboard/subscription',
            ]);

            ActivityLog::record(
                $user->id,
                'subscription.activated',
                "Activated {$plan->name} via {$lockedPayment->gateway} ({$lockedPayment->transaction_reference})"
            );

            AuditLog::logEvent(
                module: 'billing',
                action: 'subscription_activated',
                actorId: $user->id,
                targetType: Subscription::class,
                targetId: $subscription->id,
                newValues: ['plan' => $plan->slug, 'amount' => $lockedPayment->amount, 'reference' => $lockedPayment->transaction_reference]
            );

            return $subscription;
        });
    }

    /**
     * Record a failed payment and ensure NO subscription is activated.
     */
    public function recordFailedPayment(Payment $payment, string $reason = 'Payment declined by issuing bank'): Payment
    {
        $payment->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'webhook_verified' => true,
        ]);

        UserNotification::create([
            'user_id' => $payment->user_id,
            'title' => 'Payment Failed',
            'message' => "Transaction {$payment->transaction_reference} could not be completed: {$reason}.",
            'type' => 'danger',
            'action_url' => '/pricing',
        ]);

        AuditLog::logEvent(
            module: 'billing',
            action: 'payment_failed',
            actorId: $payment->user_id,
            targetType: Payment::class,
            targetId: $payment->id,
            newValues: ['reason' => $reason, 'reference' => $payment->transaction_reference],
            severity: 'warning'
        );

        return $payment->fresh();
    }

    /**
     * Cancel an active subscription (retains access until ends_at).
     */
    public function cancelSubscription(Subscription $subscription, string $reason = 'Canceled by user'): Subscription
    {
        $subscription->update([
            'status' => 'canceled',
            'auto_renew' => false,
            'canceled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        UserNotification::create([
            'user_id' => $subscription->user_id,
            'title' => 'Subscription Canceled',
            'message' => 'Auto-renewal has been turned off. You will retain Premium access until ' . ($subscription->ends_at?->format('M j, Y') ?? 'the end of your billing period') . '.',
            'type' => 'info',
            'action_url' => '/dashboard/subscription',
        ]);

        return $subscription->fresh();
    }

    /**
     * Process a refund on a succeeded payment and revoke associated subscription if full refund.
     */
    public function processRefund(Payment $payment, float $amount, string $reason, ?User $admin = null): Refund
    {
        return DB::transaction(function () use ($payment, $amount, $reason, $admin) {
            $refund = Refund::create([
                'refund_reference' => 'RFD-' . strtoupper(Str::random(8)),
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'processed_by' => $admin?->id,
                'amount' => min($amount, (float) $payment->amount),
                'currency' => $payment->currency,
                'reason' => $reason,
                'status' => 'completed',
                'refunded_at' => now(),
            ]);

            $payment->update(['status' => 'refunded']);

            if ($payment->invoice) {
                $payment->invoice->update(['status' => 'refunded']);
            }

            if ($payment->subscription) {
                $payment->subscription->update([
                    'status' => 'expired',
                    'ends_at' => now()->subMinute(),
                    'cancel_reason' => 'Refunded: ' . $reason,
                ]);
            }

            AuditLog::logEvent(
                module: 'billing',
                action: 'payment_refunded',
                actorId: $admin?->id,
                targetType: Payment::class,
                targetId: $payment->id,
                newValues: ['refund_reference' => $refund->refund_reference, 'amount' => $refund->amount, 'reason' => $reason],
                severity: 'warning'
            );

            return $refund;
        });
    }
}

