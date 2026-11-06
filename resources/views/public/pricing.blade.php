@extends('layouts.app')
@section('title', 'Pricing & Subscription Plans — Doctix')
@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <span class="badge badge-soft-primary px-3 py-2 rounded-pill mb-2">Freemium & Unlimited Plans</span>
        <h1 class="fw-bolder">Simple, Predictable SaaS Pricing</h1>
        <p class="text-secondary mx-auto" style="max-width: 640px;">
            Start with 4 free uses per individual tool every single day. Upgrade to Monthly or Annual Premium whenever your workflow demands unlimited operations and 250 MB+ uploads.
        </p>
    </div>

    <div class="row g-4 justify-content-center mb-5">
        @foreach($plans as $plan)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 rounded-4 {{ $plan->is_featured ? 'border-primary border-2 shadow' : 'border shadow-sm' }}">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="fw-bold mb-0">{{ $plan->name }}</h4>
                            @if($plan->badge_text)
                                <span class="badge {{ $plan->is_featured ? 'bg-primary' : 'bg-light text-dark border' }} rounded-pill">{{ $plan->badge_text }}</span>
                            @endif
                        </div>
                        <p class="text-secondary small mb-4">{{ $plan->description }}</p>
                        <div class="mb-4">
                            <span class="display-5 fw-bolder">${{ number_format($plan->price, 0) }}</span>
                            <span class="text-muted">/{{ $plan->billing_interval === 'free' ? 'forever' : ($plan->billing_interval === 'annual' ? 'year' : 'month') }}</span>
                        </div>
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-4 flex-grow-1">
                            @foreach($plan->features as $feat)
                                <li class="d-flex align-items-start gap-2 small">
                                    <i class="bi {{ $feat->is_included ? 'bi-check-circle-fill text-success' : 'bi-dash-circle text-muted' }} mt-1"></i>
                                    <span class="{{ $feat->is_included ? '' : 'text-muted' }}">{{ $feat->feature_name }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if($plan->billing_interval === 'free')
                            <a href="{{ auth()->check() ? route('dashboard.index') : route('register') }}" class="btn btn-outline-dark w-100 rounded-3 fw-semibold">
                                {{ auth()->check() ? 'Go to Dashboard' : 'Get Started Free' }}
                            </a>
                        @else
                            <a href="{{ route('checkout.show', $plan->slug) }}" class="btn {{ $plan->is_featured ? 'btn-doctix' : 'btn-dark' }} w-100 rounded-3 fw-semibold">
                                Subscribe to {{ $plan->name }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mx-auto" style="max-width: 820px;">
        <h3 class="fw-bold text-center mb-4">Billing & Subscription FAQs</h3>
        <div class="accordion rounded-4 border overflow-hidden" id="pricingFaq">
            @foreach($faqs as $i => $faq)
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#pfaq-{{ $faq->id }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="pfaq-{{ $faq->id }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#pricingFaq">
                        <div class="accordion-body text-secondary">{{ $faq->answer }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

