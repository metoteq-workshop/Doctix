<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'User Dashboard — Doctix')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F1F5F9; color: #0F172A; }
        .sidebar { width: 265px; min-height: 100vh; background: #0F172A; color: #94A3B8; }
        .sidebar .nav-link { color: #94A3B8; border-radius: 0.6rem; padding: 0.65rem 1rem; margin-bottom: 0.25rem; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(79, 70, 229, 0.22); color: #FFFFFF; }
        .sidebar .nav-link.active { border-left: 3px solid #6366F1; }
        .card-modern { background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 1rem; box-shadow: 0 2px 8px rgba(15,23,42,0.03); }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <aside class="sidebar d-none d-lg-flex flex-column p-3 flex-shrink-0">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none fw-bold fs-4 px-2 mb-4">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white" style="width:36px;height:36px;background:linear-gradient(135deg,#4F46E5,#7C3AED)">
                <i class="bi bi-layers-half"></i>
            </span>
            <span>Doctix</span>
        </a>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}" href="{{ route('dashboard.index') }}"><i class="bi bi-grid-1x2-fill me-2"></i>Overview</a>
            <a class="nav-link {{ request()->routeIs('dashboard.usage') ? 'active' : '' }}" href="{{ route('dashboard.usage') }}"><i class="bi bi-bar-chart-steps me-2"></i>Daily Usage & Limits</a>
            <a class="nav-link {{ request()->routeIs('dashboard.documents') ? 'active' : '' }}" href="{{ route('dashboard.documents') }}"><i class="bi bi-folder2-open me-2"></i>My Documents</a>
            <a class="nav-link {{ request()->routeIs('dashboard.subscription') ? 'active' : '' }}" href="{{ route('dashboard.subscription') }}"><i class="bi bi-credit-card-2-front me-2"></i>Subscription & Billing</a>
            <a class="nav-link {{ request()->routeIs('dashboard.activities') ? 'active' : '' }}" href="{{ route('dashboard.activities') }}"><i class="bi bi-clock-history me-2"></i>Recent Activities</a>
            <a class="nav-link {{ request()->routeIs('dashboard.notifications') ? 'active' : '' }}" href="{{ route('dashboard.notifications') }}"><i class="bi bi-bell me-2"></i>Notifications</a>
            <a class="nav-link {{ request()->routeIs('dashboard.tickets') ? 'active' : '' }}" href="{{ route('dashboard.tickets') }}"><i class="bi bi-life-preserver me-2"></i>Support Tickets</a>
            <a class="nav-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}" href="{{ route('dashboard.profile') }}"><i class="bi bi-person-gear me-2"></i>Account & Security</a>
            <hr class="border-secondary">
            <a class="nav-link text-info" href="{{ route('tools.index') }}"><i class="bi bi-box-arrow-up-right me-2"></i>Explore 60+ Tools</a>
            @if(auth()->user()->isAdmin())
                <a class="nav-link text-warning" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</a>
            @endif
        </nav>

        <div class="p-3 rounded-4 mt-3" style="background: rgba(255,255,255,0.05);">
            @if(auth()->user()->hasActivePremiumSubscription())
                <span class="badge bg-success mb-1"><i class="bi bi-patch-check-fill me-1"></i>Premium Unlimited</span>
                <p class="small text-light mb-0">Unlimited access to all tools enabled.</p>
            @else
                <span class="badge bg-primary mb-1">Free Plan</span>
                <p class="small text-secondary mb-2">4 free uses per individual tool per day.</p>
                <a href="{{ route('pricing') }}" class="btn btn-sm btn-primary w-100 fw-semibold">Upgrade to Unlimited</a>
            @endif
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('tools.index') }}" class="btn btn-sm btn-outline-primary rounded-3"><i class="bi bi-plus-circle me-1"></i>Launch a Tool</a>
                <span class="text-muted small d-none d-md-inline">Daily quota resets at 00:00 {{ config('app.timezone') }}</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-semibold small">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                </form>
            </div>
        </header>

        <div class="p-4 flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger rounded-4 border-0 shadow-sm">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

