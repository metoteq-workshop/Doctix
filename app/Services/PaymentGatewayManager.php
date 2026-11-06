<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayManager
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    /**
     * Verify HMAC webhook signature for Stripe, PayPal, Paystack, or Flutterwave.
     */
    public function verifyWebhookSignature(string $gatewayCode, string $rawPayload, ?string $signatureHeader): bool
    {
        if (!$signatureHeader) {
            return false;
        }

        $gateway = PaymentGateway::where('code', $gatewayCode)->first();
        if (!$gateway) {
            return false;
        }

        $secret = '';
        try {
            $secret = $gateway->webhook_secret ? decrypt($gateway->webhook_secret) : ('whsec_' . $gatewayCode . '_secret');
        } catch (\Throwable) {
            $secret = (string) $gateway->webhook_secret ?: ('whsec_' . $gatewayCode . '_secret');
        }

        $expectedHmac = hash_hmac('sha256', $rawPayload, $secret);
        return hash_equals($expectedHmac, $signatureHeader);
    }

    /**
     * Process an incoming verified gateway webhook payload.
     */
    public function handleWebhook(string $gatewayCode, Request $request): array
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('X-Webhook-Signature')
            ?? $request->header('Stripe-Signature')
            ?? $request->header('X-Paystack-Signature')
            ?? $request->header('Verif-Hash');

        if (!$this->verifyWebhookSignature($gatewayCode, $rawPayload, $signature)) {
            return [
                'ok' => false,
                'status' => 401,
                'message' => 'Invalid webhook signature.',
            ];
        }

        $data = $request->json()->all();
        $reference = $data['transaction_reference'] ?? $data['data']['reference'] ?? null;
        $event = $data['event'] ?? 'payment.succeeded';
        $gatewayTxId = $data['gateway_transaction_id'] ?? $data['data']['id'] ?? ('gw_' . uniqid());

        if (!$reference) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'Missing transaction_reference in webhook payload.',
            ];
        }

        $payment = Payment::where('transaction_reference', $reference)->first();
        if (!$payment) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Payment record not found.',
            ];
        }

        if (in_array($event, ['payment.succeeded', 'charge.success', 'checkout.session.completed'], true)) {
            $subscription = $this->subscriptionService->confirmVerifiedPayment($payment, (string) $gatewayTxId, [
                'card_brand' => $data['card_brand'] ?? 'Visa',
                'card_last4' => $data['card_last4'] ?? '4242',
            ]);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Subscription activated via verified webhook.',
                'subscription_id' => $subscription->id,
            ];
        }

        if (in_array($event, ['payment.failed', 'charge.failed'], true)) {
            $this->subscriptionService->recordFailedPayment($payment, $data['reason'] ?? 'Gateway reported charge failure');

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Payment failure recorded; subscription not activated.',
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Webhook event acknowledged.',
        ];
    }
}

