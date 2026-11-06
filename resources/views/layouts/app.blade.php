<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = \App\Models\SystemSetting::getValue('site_name', 'Doctix');
        $defaultTitle = \App\Models\SystemSetting::getValue('meta_title', 'Doctix — Online PDF, Image, Document & AI Productivity Platform');
        $defaultDesc = \App\Models\SystemSetting::getValue('meta_description', 'Convert, merge, split, compress, and edit PDFs, images, and documents online. Use every tool 4 times daily for free.');
    @endphp

    <title>@yield('title', $defaultTitle)</title>
    <meta name="description" content="@yield('meta_description', $defaultDesc)">
    <meta name="keywords" content="@yield('meta_keywords', 'pdf tools, merge pdf, compress pdf, pdf to word, image compressor, ai pdf summarizer, doctix')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Twitter Cards -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', $defaultTitle)">
    <meta property="og:description" content="@yield('meta_description', $defaultDesc)">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $defaultTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $defaultDesc)">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        :root {
            --dtx-primary: #4F46E5;
            --dtx-primary-dark: #4338CA;
            --dtx-accent: #7C3AED;
            --dtx-dark: #0F172A;
            --dtx-slate: #475569;
            --dtx-bg: #F8FAFC;
        }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--dtx-bg);
            color: #1E293B;
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            letter-spacing: -0.02em;
        }
        .navbar-doctix {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        }
        .btn-doctix {
            background: linear-gradient(135deg, var(--dtx-primary), var(--dtx-accent));
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 0.625rem 1.35rem;
            border-radius: 0.65rem;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.28);
            transition: all 0.2s ease;
        }
        .btn-doctix:hover {
            transform: translateY(-1px);
            color: #fff;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.38);
        }
        .hero-gradient {
            background: radial-gradient(circle at 15% 20%, rgba(79, 70, 229, 0.10) 0%, transparent 45%),
                        radial-gradient(circle at 85% 30%, rgba(124, 58, 237, 0.10) 0%, transparent 45%),
                        linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
        }
        .tool-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 1rem;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }
        .tool-card:hover {
            transform: translateY(-4px);
            border-color: #CBD5E1;
            box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.08);
            color: inherit;
        }
        .tool-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }
        .dropzone-area {
            border: 2px dashed #94A3B8;
            border-radius: 1.25rem;
            background: #FFFFFF;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .dropzone-area:hover, .dropzone-area.dragover {
            border-color: var(--dtx-primary);
            background: rgba(79, 70, 229, 0.03);
        }
        .badge-soft-primary {
            background: rgba(79, 70, 229, 0.1);
            color: var(--dtx-primary);
        }
        .badge-soft-success {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }
        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.14);
            color: #D97706;
        }
        .footer-dark {
            background: #0F172A;
            color: #94A3B8;
        }
        .footer-dark a {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.15s;
        }
        .footer-dark a:hover {
            color: #FFFFFF;
        }
    </style>

    @yield('schema')
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-doctix sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-dark" href="{{ route('home') }}">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white" style="width:38px;height:38px;background:linear-gradient(135deg,#4F46E5,#7C3AED)">
                    <i class="bi bi-layers-half"></i>
                </span>
                <span>{{ $siteName }}</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2 fw-medium">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('tools.*') ? 'text-primary fw-semibold' : '' }}" href="{{ route('tools.index') }}"><i class="bi bi-grid-fill me-1"></i>All Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('tools.index', ['category' => 'pdf-tools']) }}">PDF Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('tools.index', ['category' => 'image-tools']) }}">Image Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('tools.index', ['category' => 'ai-tools']) }}"><i class="bi bi-stars text-warning me-1"></i>AI Suite</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pricing') ? 'text-primary fw-semibold' : '' }}" href="{{ route('pricing') }}">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('blog.*') ? 'text-primary fw-semibold' : '' }}" href="{{ route('blog.index') }}">Resources</a></li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm rounded-3 px-3">
                                <i class="bi bi-shield-lock me-1"></i>Admin
                            </a>
                        @endif
                        <a href="{{ route('dashboard.index') }}" class="btn btn-light border fw-semibold rounded-3 px-3">
                            <i class="bi bi-speedometer2 me-1 text-primary"></i>Dashboard
                        </a>
                        @if(!auth()->user()->hasActivePremiumSubscription())
                            <a href="{{ route('pricing') }}" class="btn btn-doctix btn-sm">
                                <i class="bi bi-lightning-charge-fill me-1"></i>Upgrade
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-link text-decoration-none text-dark fw-semibold">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-doctix">Start for Free</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Notifications -->
    @if(session('success') || session('error') || session('info') || $errors->any())
        <div class="container mt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    @if(session('upgrade_required'))
                        <a href="{{ route('pricing') }}" class="btn btn-sm btn-danger ms-3 fw-semibold">Upgrade to Unlimited Premium</a>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Professional Footer -->
    <footer class="footer-dark pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-secondary border-opacity-25">
                <div class="col-lg-4">
                    <a class="d-inline-flex align-items-center gap-2 fw-bold fs-4 text-white text-decoration-none mb-3" href="{{ route('home') }}">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white" style="width:34px;height:34px;background:linear-gradient(135deg,#4F46E5,#7C3AED)">
                            <i class="bi bi-layers-half"></i>
                        </span>
                        <span>{{ $siteName }}</span>
                    </a>
                    <p class="small text-secondary mb-3">
                        Enterprise-grade online PDF editing, document conversion, image optimization, and AI-powered productivity tools. Use every individual tool 4 times daily for free, or upgrade to Unlimited Premium.
                    </p>
                    <div class="d-flex gap-3 small">
                        <span class="badge bg-dark border border-secondary text-light"><i class="bi bi-shield-check text-success me-1"></i>256-Bit TLS Encrypted</span>
                        <span class="badge bg-dark border border-secondary text-light"><i class="bi bi-clock-history text-info me-1"></i>Auto File Purge</span>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Popular Tools</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('tools.show', 'merge-pdf') }}">Merge PDF</a></li>
                        <li><a href="{{ route('tools.show', 'compress-pdf') }}">Compress PDF</a></li>
                        <li><a href="{{ route('tools.show', 'pdf-to-word') }}">PDF to Word</a></li>
                        <li><a href="{{ route('tools.show', 'sign-pdf') }}">Sign PDF</a></li>
                        <li><a href="{{ route('tools.show', 'ai-pdf-summarizer') }}">AI PDF Summarizer</a></li>
                        <li><a href="{{ route('tools.show', 'qr-code-generator') }}">QR Code Generator</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Categories</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('tools.index', ['category' => 'pdf-tools']) }}">PDF Tools</a></li>
                        <li><a href="{{ route('tools.index', ['category' => 'image-tools']) }}">Image Tools</a></li>
                        <li><a href="{{ route('tools.index', ['category' => 'document-tools']) }}">Document Tools</a></li>
                        <li><a href="{{ route('tools.index', ['category' => 'ai-tools']) }}">AI-Powered Tools</a></li>
                        <li><a href="{{ route('tools.index', ['category' => 'productivity-tools']) }}">Productivity Utilities</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Company</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('about') }}">About Us</a></li>
                        <li><a href="{{ route('features') }}">Platform Features</a></li>
                        <li><a href="{{ route('pricing') }}">Pricing Plans</a></li>
                        <li><a href="{{ route('blog.index') }}">Blog & Guides</a></li>
                        <li><a href="{{ route('help') }}">Help Center</a></li>
                        <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-semibold mb-3">Legal & Trust</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('legal.privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.terms') }}">Terms & Conditions</a></li>
                        <li><a href="{{ route('legal.cookies') }}">Cookie Policy</a></li>
                        <li><a href="{{ route('legal.refund') }}">Refund Policy</a></li>
                        <li><a href="{{ route('faq') }}">FAQs</a></li>
                        <li><a href="{{ route('sitemap') }}">XML Sitemap</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-4 small">
                <div>&copy; {{ date('Y') }} {{ $siteName }} Cloud Inc. All rights reserved.</div>
                <div class="mt-2 mt-md-0">Engineered with Laravel {{ app()->version() }} &bull; Server Timezone: {{ config('app.timezone') }}</div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

