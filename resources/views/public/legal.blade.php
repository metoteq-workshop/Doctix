@extends('layouts.app')
@section('title', $title . ' — Doctix')
@section('content')
<div class="container py-5" style="max-width: 840px;">
    <div class="bg-white p-4 p-lg-5 rounded-4 border shadow-sm">
        <h1 class="fw-bolder mb-3">{{ $title }}</h1>
        <p class="text-muted small mb-4">Effective Date: {{ date('F j, Y') }} &bull; Doctix Cloud Inc.</p>
        <h5 class="fw-bold mt-4">1. Data Security & Automatic File Deletion</h5>
        <p class="text-secondary">All files uploaded to Doctix are stored in isolated private storage, processed exclusively for the requested tool operation, and automatically purged after 24 hours. No user can access another user's uploaded or processed documents.</p>
        <h5 class="fw-bold mt-4">2. Freemium Daily Allowance & Fair Use</h5>
        <p class="text-secondary">Registered Free Plan accounts receive four (4) completed operations per individual tool per calendar day, resetting at midnight ({{ config('app.timezone') }}). Premium subscribers receive unlimited daily access across all tools while their subscription remains active.</p>
        <h5 class="fw-bold mt-4">3. Subscriptions, Cancellation & Refunds</h5>
        <p class="text-secondary">Subscriptions may be canceled at any time from your User Dashboard. Upon cancellation, your account retains Premium entitlement through the end of the paid billing cycle. Refund requests are handled in accordance with consumer protection laws via our Support desk.</p>
    </div>
</div>
@endsection

