@extends('layouts.app')
@section('title', 'Reset Password — Doctix')
@section('content')
<div class="container py-5" style="max-width: 460px;">
    <div class="card border rounded-4 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <h3 class="fw-bolder mb-2">Reset Your Password</h3>
            <p class="text-secondary small mb-4">Enter your registered email address and we will send you a password reset link.</p>
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" required class="form-control">
                </div>
                <button type="submit" class="btn btn-doctix w-100">Send Password Reset Link</button>
            </form>
        </div>
    </div>
</div>
@endsection

