@extends('layouts.app')
@section('title', 'Frequently Asked Questions — Doctix')
@section('content')
<div class="container py-5" style="max-width: 840px;">
    <div class="text-center mb-5">
        <h1 class="fw-bolder">Frequently Asked Questions</h1>
        <p class="text-secondary">Everything you need to know about Doctix tools, daily quotas, and Premium memberships.</p>
    </div>
    <div class="accordion rounded-4 border overflow-hidden shadow-sm" id="faqPageAcc">
        @foreach($faqs as $i => $faq)
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqp-{{ $faq->id }}">
                        {{ $faq->question }}
                    </button>
                </h2>
                <div id="faqp-{{ $faq->id }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqPageAcc">
                    <div class="accordion-body text-secondary">{{ $faq->answer }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

