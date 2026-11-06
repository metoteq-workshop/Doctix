<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Control Center — Doctix')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F1F5F9; color: #0F172A; }
        .admin-sidebar { width: 270px; min-height: 100vh; background: #090D16; color: #94A3B8; }
        .admin-sidebar .nav-link { color: #94A3B8; border-radius: 0.5rem; padding: 0.65rem 1rem; margin-bottom: 0.2rem; font-weight: 500; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: #1E293B; color: #FFFFFF; }
        .card-modern { background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 1rem; box-shadow: 0 2px 6px rgba(15,23,42,0.04); }
    </style>
</head>
<body>
<div class="d-flex">
    <aside class="admin-sidebar d-none d-lg-flex flex-column p-3 flex-shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none fw-bold fs-5 px-2 mb-4">
            <span class="badge bg-danger rounded-2 p-2"><i class="bi bi-shield-lock-fill"></i></span>
            <span>Doctix Admin</span>
        </a>
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Analytics & Overview</a>
            <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}"><i class="bi bi-people-fill me-2"></i>Users & Quota Reset</a>
            <a class="nav-link {{ request()->routeIs('admin.tools*') ? 'active' : '' }}" href="{{ route('admin.tools') }}"><i class="bi bi-tools me-2"></i>Tools & Engines</a>
            <a class="nav-link {{ request()->routeIs('admin.plans*') ? 'active' : '' }}" href="{{ route('admin.plans') }}"><i class="bi bi-gem me-2"></i>Plans & Subscriptions</a>
            <a class="nav-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}" href="{{ route('admin.payments') }}"><i class="bi bi-cash-coin me-2"></i>Payments & Refunds</a>
            <a class="nav-link {{ request()->routeIs('admin.cms*') ? 'active' : '' }}" href="{{ route('admin.cms') }}"><i class="bi bi-File-earmark-richtext me-2"></i>CMS, Blog & Emails</a>
            <a class="nav-link {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}" href="{{ route('admin.tickets') }}"><i class="bi bi-headset me-2"></i>Support Tickets</a>
            <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i class="bi bi-sliders me-2"></i>Settings, Gateways & AI</a>
            <a class="nav-link {{ request()->routeIs('admin.logs*') ? 'active' : '' }}" href="{{ route('admin.logs') }}"><i class="bi bi-journal-code me-2"></i>Audit Logs & Health</a>
            <hr class="border-secondary">
            <a class="nav-link text-info" href="{{ route('home') }}"><i class="bi bi-globe me-2"></i>Public Website</a>
            <a class="nav-link text-light" href="{{ route('dashboard.index') }}"><i class="bi bi-person-workspace me-2"></i>User Dashboard</a>
        </nav>
    </aside>

    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        <header class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">@yield('header_title', 'Platform Administration')</h5>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.analytics.export') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Export Financial CSV</a>
                <span class="badge bg-dark">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">@csrf<button class="btn btn-sm btn-outline-danger">Logout</button></form>
            </div>
        </header>

        <div class="p-4 flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

