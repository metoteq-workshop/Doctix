@extends('layouts.dashboard')
@section('title', 'Subscription & Billing Management — Doctix')
@section('content')
<h3 class="fw-bolder mb-4">Subscription & Billing Center</h3>

<!-- Current Plan Card -->
<div class="card-modern p-4 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <span class="badge {{ $activeSubscription ? 'bg-success' : 'bg-primary' }} mb-2">
                {{ $activeSubscription ? 'Active Premium Entitlement' : 'Free Tier Account' }}
            </span>
            <h4 class="fw-bolder mb-1">{{ $currentPlan?->name ?? 'Free Plan' }}</h4>
            @if($activeSubscription)
                <p class="text-secondary small mb-0">
                    Gateway: <strong class="text-uppercase">{{ $activeSubscription->payment_gateway }}</strong> &bull;
                    Valid Until: <strong>{{ $activeSubscription->ends_at?->format('F j, Y H:i') }}</strong> &bull;
                    Auto-Renew: <strong>{{ $activeSubscription->auto_renew ? 'Enabled' : 'Canceled' }}</strong>
                </p>
            @else
                <p class="text-secondary small mb-0">
                    You currently have 4 free uses per individual tool every day. Upgrade below for unlimited operations across all 60+ tools and 250 MB+ file uploads.
                </p>
            @endif
        </div>
        <div class="col-lg-4 text-lg-end">
            @if($activeSubscription && $activeSubscription->status === 'active')
                <form action="{{ route('subscription.cancel') }}" method="POST" onsubmit="return confirm('Cancel auto-renewal? You will keep access until the end of your billing period.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger rounded-3">Cancel Subscription</button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Available Upgrade Plans -->
<div class="row g-4 mb-5">
    @foreach($plans as $plan)
        <div class="col-md-4">
            <div class="card-modern p-4 h-100 d-flex flex-column {{ $plan->is_featured ? 'border-primary border-2' : '' }}">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="fw-bold mb-0">{{ $plan->name }}</h5>
                    @if($plan->badge_text)
                        <span class="badge bg-primary rounded-pill">{{ $plan->badge_text }}</span>
                    @endif
                </div>
                <div class="fs-3 fw-bolder mb-2">${{ number_format($plan->price, 2) }} <small class="fs-6 text-muted fw-normal">/{{ $plan->billing_interval }}</small></div>
                <p class="text-secondary small flex-grow-1">{{ $plan->description }}</p>
                @if($plan->billing_interval === 'free')
                    <button class="btn btn-light border w-100" disabled>Base Plan</button>
                @else
                    <a href="{{ route('checkout.show', $plan->slug) }}" class="btn btn-primary w-100 fw-semibold">
                        {{ $activeSubscription ? 'Switch / Renew ' . $plan->name : 'Upgrade to ' . $plan->name }}
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>

<!-- Billing History & Invoices -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Payment Transactions</h5>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Plan</th>
                            <th>Gateway</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->transaction_reference }}</td>
                                <td>{{ $p->plan?->name ?? 'Plan' }}</td>
                                <td class="text-uppercase">{{ $p->gateway }}</td>
                                <td class="fw-bold">${{ number_format($p->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $p->status === 'succeeded' ? 'success' : ($p->status === 'failed' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $p->created_at?->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No payment transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Tax Invoices</h5>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                                <td>${{ number_format($inv->total, 2) }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($inv->status) }}</span></td>
                                <td>
                                    <a href="{{ route('dashboard.invoice.show', $inv->invoice_number) }}" class="btn btn-sm btn-outline-dark">View Invoice</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No invoices generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

