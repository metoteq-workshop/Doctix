@extends('layouts.dashboard')
@section('title', 'Secure Subscription Checkout — Doctix')
@section('content')
<div class="mx-auto" style="max-width: 780px;">
    <h3 class="fw-bolder mb-4">Complete Your Upgrade to {{ $plan->name }}</h3>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card-modern p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock-fill text-success me-2"></i>Select Payment Gateway</h5>
                <form action="{{ route('checkout.process', $plan->slug) }}" method="POST">
                    @csrf
                    <div class="d-flex flex-column gap-3 mb-4">
                        @foreach($gateways as $idx => $gw)
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-3 cursor-pointer">
                                <input type="radio" name="gateway" value="{{ $gw->code }}" class="form-check-input mt-0" {{ $idx === 0 ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-bold">{{ $gw->display_name }}</div>
                                    <div class="text-secondary small">{{ $gw->description }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Promo / Coupon Code (Optional — Try <code>WELCOME20</code>)</label>
                        <input type="text" name="coupon_code" class="form-control" placeholder="WELCOME20">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Last 4 Digits of Card (For Invoice Receipt)</label>
                        <input type="text" name="card_last4" value="4242" maxlength="4" class="form-control">
                        <div class="form-text small">Raw card numbers are tokenized directly by the payment gateway and never stored on Doctix servers.</div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                        Pay ${{ number_format($plan->price, 2) }} {{ $plan->currency }} & Activate Unlimited Access
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card-modern p-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Order Summary</h6>
                <h4 class="fw-bolder">{{ $plan->name }}</h4>
                <p class="text-secondary small">{{ $plan->description }}</p>
                <hr>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Billing Cycle</span>
                    <strong class="text-capitalize">{{ $plan->billing_interval }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Daily Tool Quota</span>
                    <strong class="text-success">Unlimited (All 60+ Tools)</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 small">
                    <span>Max Upload Size</span>
                    <strong>{{ $plan->max_file_size_mb }} MB</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total Due Today</span>
                    <span class="fs-4 fw-bolder text-primary">${{ number_format($plan->price, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

