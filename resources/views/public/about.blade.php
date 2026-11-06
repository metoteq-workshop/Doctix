@extends('layouts.app')
@section('title', 'About Doctix — Modern Document Productivity SaaS')
@section('content')
<div class="container py-5">
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <span class="text-primary fw-bold text-uppercase small">Our Mission</span>
            <h1 class="fw-bolder mb-3">Empowering Millions to Work Smarter with Documents</h1>
            <p class="text-secondary">
                Doctix was founded on a simple belief: essential PDF, image, document conversion, and AI analysis tools should be accessible in any web browser without bloated desktop installations or unfair paywalls.
            </p>
            <p class="text-secondary mb-0">
                Our transparent freemium model grants every registered user 4 daily operations per individual tool — while our Premium tiers deliver unlimited throughput for legal, finance, engineering, and operations teams worldwide.
            </p>
        </div>
        <div class="col-lg-6">
            <div class="row g-4 text-center">
                <div class="col-6">
                    <div class="p-4 bg-white rounded-4 border shadow-sm">
                        <div class="display-5 fw-bolder text-primary">{{ $totalTools }}+</div>
                        <div class="text-secondary small fw-semibold">Active Cloud Tools</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 bg-white rounded-4 border shadow-sm">
                        <div class="display-5 fw-bolder text-success">99.9%</div>
                        <div class="text-secondary small fw-semibold">Service Uptime SLA</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 bg-white rounded-4 border shadow-sm">
                        <div class="display-5 fw-bolder text-dark">256-Bit</div>
                        <div class="text-secondary small fw-semibold">TLS Encryption</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-4 bg-white rounded-4 border shadow-sm">
                        <div class="display-5 fw-bolder text-warning">4/Day</div>
                        <div class="text-secondary small fw-semibold">Free Uses Per Tool</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

