@extends('layouts.dashboard')
@section('title', 'Account Settings & Security — Doctix')
@section('content')
<h3 class="fw-bolder mb-4">Profile, Security & Active Sessions</h3>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-modern p-4 mb-4">
            <h5 class="fw-bold mb-3">Profile Information</h5>
            <form action="{{ route('dashboard.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Full Name</label>
                    <input type="text" name="name" value="{{ $user->name }}" required class="form-control">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Company</label>
                        <input type="text" name="company" value="{{ $user->profile?->company }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Job Title</label>
                        <input type="text" name="job_title" value="{{ $user->profile?->job_title }}" class="form-control">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Country</label>
                        <input type="text" name="country" value="{{ $user->profile?->country }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Profile Photo</label>
                        <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary rounded-3">Save Profile</button>
            </form>
        </div>

        <div class="card-modern p-4">
            <h5 class="fw-bold mb-2">Two-Factor Authentication (2FA)</h5>
            <p class="text-secondary small mb-3">Protect your account with an additional layer of verification during sign-in.</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge {{ $user->two_factor_enabled ? 'bg-success' : 'bg-secondary' }}">
                    {{ $user->two_factor_enabled ? '2FA Enabled' : '2FA Disabled' }}
                </span>
                <form action="{{ route('dashboard.profile.2fa') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $user->two_factor_enabled ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                        {{ $user->two_factor_enabled ? 'Disable 2FA' : 'Enable 2FA' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-modern p-4 mb-4">
            <h5 class="fw-bold mb-3">Change Password</h5>
            <form action="{{ route('dashboard.profile.password') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Current Password</label>
                    <input type="password" name="current_password" required class="form-control">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">New Password</label>
                        <input type="password" name="password" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-dark rounded-3">Update Password</button>
            </form>
        </div>

        <div class="card-modern p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Active Sessions</h5>
                <form action="{{ route('dashboard.profile.sessions.revoke') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Sign Out Other Devices</button>
                </form>
            </div>
            <p class="text-secondary small mb-0">Currently signed in from IP <code>{{ request()->ip() }}</code>.</p>
        </div>

        <div class="card-modern p-4 border-danger">
            <h6 class="fw-bold text-danger">Delete Account</h6>
            <p class="small text-secondary">Permanently delete your account, files, and subscription history.</p>
            <form action="{{ route('dashboard.profile.delete') }}" method="POST" class="d-flex gap-2" onsubmit="return confirm('Permanently delete your account?');">
                @csrf
                @method('DELETE')
                <input type="password" name="password" required placeholder="Confirm current password" class="form-control form-control-sm">
                <button type="submit" class="btn btn-sm btn-danger flex-shrink-0">Delete Account</button>
            </form>
        </div>
    </div>
</div>
@endsection

