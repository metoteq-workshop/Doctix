@extends('layouts.app')
@section('title', 'Sign In to Your Account — Doctix')
@section('content')
<div class="container py-5" style="max-width: 460px;">
    <div class="card border rounded-4 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <h2 class="fw-bolder mb-1">Welcome Back</h2>
                <p class="text-secondary small">Sign in to access your tools and daily usage dashboard.</p>
            </div>

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="form-control">
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label small fw-semibold">Password</label>
                        <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
                    </div>
                    <input type="password" name="password" required class="form-control">
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label small" for="rememberMe">Keep me signed in</label>
                </div>
                <button type="submit" class="btn btn-doctix w-100 py-2 mb-3">Sign In</button>
            </form>

            <div class="text-center small text-secondary">
                Don't have an account? <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-none">Create Free Account</a>
            </div>
        </div>
    </div>
</div>
@endsection

