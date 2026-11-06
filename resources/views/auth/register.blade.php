@extends('layouts.app')
@section('title', 'Create Your Free Account — Doctix')
@section('content')
<div class="container py-5" style="max-width: 500px;">
    <div class="card border rounded-4 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill mb-2">4 Free Uses / Tool / Day</span>
                <h2 class="fw-bolder mb-1">Create Your Free Account</h2>
                <p class="text-secondary small">Get instant access to 60+ PDF, Image, Document & AI tools.</p>
            </div>

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="Alex Morgan">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Work or Personal Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="alex@company.com">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Company / Organization (Optional)</label>
                    <input type="text" name="company" value="{{ old('company') }}" class="form-control" placeholder="Acme Inc.">
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Password</label>
                        <input type="password" name="password" required class="form-control" placeholder="Min. 8 characters">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-doctix w-100 py-2 mb-3">Create Free Account</button>
            </form>

            <div class="text-center small text-secondary">
                Already registered? <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-none">Sign In</a>
            </div>
        </div>
    </div>
</div>
@endsection

