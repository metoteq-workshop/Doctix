@extends('layouts.app')
@section('title', 'Contact Us & Support — Doctix')
@section('content')
<div class="container py-5" style="max-width: 880px;">
    <div class="text-center mb-5">
        <h1 class="fw-bolder">Get in Touch with Doctix Support</h1>
        <p class="text-secondary">Have questions about our tools, enterprise licensing, or billing? Submit a ticket below.</p>
    </div>

    <div class="card border rounded-4 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <form action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Your Name</label>
                        <input type="text" name="name" required value="{{ auth()->user()->name ?? old('name') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="email" required value="{{ auth()->user()->email ?? old('email') }}" class="form-control">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Subject</label>
                        <input type="text" name="subject" required value="{{ old('subject') }}" class="form-control" placeholder="How can we help?">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Department</label>
                        <select name="category" class="form-select">
                            <option value="general">General Inquiry</option>
                            <option value="billing">Subscription & Billing</option>
                            <option value="technical">Technical Support</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Message</label>
                        <textarea name="message" rows="5" required class="form-control" placeholder="Describe your question or feedback...">{{ old('message') }}</textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-doctix px-5">Send Message</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

