@extends('layouts.app')

@section('title', \App\Models\SystemSetting::getValue('meta_title', 'Doctix — Free Online PDF Editor, Converter & AI Document Tools'))
@section('meta_description', \App\Models\SystemSetting::getValue('meta_description', 'Access 60+ online PDF, Image, Document, and AI productivity tools. Use every individual tool 4 times daily for free or unlock Unlimited Premium.'))

@section('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SoftwareApplication",
  "name": "Doctix",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "description": "All-in-one online PDF, Image, Document, and AI productivity SaaS platform.",
  "offers": {
    "@@type": "Offer",
    "price": "0.00",
    "priceCurrency": "USD"
  }
}
</script>
@endsection

@section('content')
<!-- 1-5. Hero Section + Prominent Live Tool Search Bar -->
<section class="hero-gradient py-5 border-bottom">
    <div class="container py-lg-5 text-center">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill badge-soft-primary fw-semibold small mb-3">
            <i class="bi bi-stars"></i>
            <span>{{ $totalToolsCount }}+ Cloud Document, PDF, Image & AI Tools — 4 Free Uses Per Tool Every Day</span>
        </div>

        <h1 class="display-4 fw-bolder text-dark mb-3 mx-auto" style="max-width: 880px;">
            {{ \App\Models\SystemSetting::getValue('hero_headline', 'Every Document Tool You Need, Engineered for Speed.') }}
        </h1>

        <p class="lead text-secondary mx-auto mb-4" style="max-width: 720px;">
            {{ \App\Models\SystemSetting::getValue('hero_subheadline', 'Merge, split, compress, convert, sign, and summarize PDFs, images, and Office documents in seconds. Use every individual tool 4 times daily for free.') }}
        </p>

        <!-- Prominent Search Bar -->
        <div class="mx-auto mb-4" style="max-width: 680px;">
            <form action="{{ route('tools.index') }}" method="GET" class="position-relative shadow-sm rounded-4 bg-white p-2 border d-flex align-items-center">
                <i class="bi bi-search text-muted fs-5 ms-3 me-2"></i>
                <input type="text" name="q" class="form-control border-0 shadow-none fs-6" placeholder="Search 60+ tools (e.g., Merge PDF, PDF to Word, AI Summarizer, QR Code)...">
                <button type="submit" class="btn btn-doctix px-4 rounded-3">Find Tool</button>
            </form>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 small">
                <span class="text-muted">Trending:</span>
                <a href="{{ route('tools.show', 'merge-pdf') }}" class="badge bg-white text-dark border text-decoration-none">Merge PDF</a>
                <a href="{{ route('tools.show', 'compress-pdf') }}" class="badge bg-white text-dark border text-decoration-none">Compress PDF</a>
                <a href="{{ route('tools.show', 'pdf-to-word') }}" class="badge bg-white text-dark border text-decoration-none">PDF to Word</a>
                <a href="{{ route('tools.show', 'ai-pdf-summarizer') }}" class="badge bg-white text-dark border text-decoration-none">AI PDF Summarizer</a>
                <a href="{{ route('tools.show', 'compress-image') }}" class="badge bg-white text-dark border text-decoration-none">Compress Image</a>
            </div>
        </div>

        <!-- Prominent CTAs -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <a href="{{ route('tools.index') }}" class="btn btn-doctix btn-lg px-4"><i class="bi bi-grid-fill me-2"></i>Explore Tools</a>
            @guest
                <a href="{{ route('register') }}" class="btn btn-outline-dark btn-lg px-4 rounded-3 fw-semibold">Start for Free</a>
            @endguest
            <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-lg px-4 rounded-3 fw-semibold"><i class="bi bi-gem me-2"></i>Upgrade to Premium</a>
        </div>
    </div>
</section>

<!-- 6. Popular Tools Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="text-primary fw-bold text-uppercase small">Most Used Daily</span>
                <h2 class="fw-bold mb-1">Popular Document & PDF Tools</h2>
                <p class="text-secondary mb-0">Each tool includes 4 free daily operations per registered account — independent of other tools.</p>
            </div>
            <a href="{{ route('tools.index') }}" class="btn btn-outline-primary rounded-3 mt-3 mt-md-0 fw-semibold">
                View All {{ $totalToolsCount }} Tools <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($popularTools as $tool)
                <div class="col-sm-6 col-lg-3">
                    <x-tool-card :tool="$tool" />
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 7. Tools Categorized by Functionality -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Complete Suite</span>
            <h2 class="fw-bold">Tools Categorized by Functionality</h2>
            <p class="text-secondary mx-auto" style="max-width: 640px;">
                Switch between specialized workspaces for PDF manipulation, raster/vector image processing, Office document conversion, AI analysis, and developer utilities.
            </p>
        </div>

        <ul class="nav nav-pills justify-content-center gap-2 mb-4" id="categoryTabs" role="tablist">
            @foreach($categories as $idx => $cat)
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-semibold {{ $idx === 0 ? 'active' : '' }}"
                            id="tab-{{ $cat->slug }}" data-bs-toggle="pill" data-bs-target="#pane-{{ $cat->slug }}" type="button" role="tab">
                        <i class="bi {{ $cat->icon }} me-1"></i>{{ $cat->name }}
                        <span class="badge bg-light text-dark ms-1">{{ $cat->activeTools->count() }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($categories as $idx => $cat)
                <div class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}" id="pane-{{ $cat->slug }}" role="tabpanel">
                    <div class="row g-4">
                        @foreach($cat->activeTools->take(8) as $tool)
                            <div class="col-sm-6 col-lg-3">
                                <x-tool-card :tool="$tool" />
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('tools.index', ['category' => $cat->slug]) }}" class="btn btn-light border fw-semibold rounded-3 px-4">
                            Explore All {{ $cat->name }} ({{ $cat->activeTools->count() }}) <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 8. How-It-Works Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Simple 3-Step Workflow</span>
            <h2 class="fw-bold">How Doctix Works</h2>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary fw-bold fs-4 mb-3" style="width:60px;height:60px;">1</div>
                    <h5 class="fw-bold">Select Any Tool & Upload</h5>
                    <p class="text-secondary small mb-0">Pick from {{ $totalToolsCount }}+ specialized tools and drag-and-drop your PDF, Word, Excel, or image files into the encrypted workspace.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary fw-bold fs-4 mb-3" style="width:60px;height:60px;">2</div>
                    <h5 class="fw-bold">Instant Cloud Processing</h5>
                    <p class="text-secondary small mb-0">Our high-speed document engine executes your merge, conversion, compression, or AI analysis in seconds with real-time progress tracking.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary fw-bold fs-4 mb-3" style="width:60px;height:60px;">3</div>
                    <h5 class="fw-bold">Download Securely</h5>
                    <p class="text-secondary small mb-0">Download your processed result via a private signed token. Temporary files are automatically purged after 24 hours for complete privacy.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9 & 10. Key Platform Benefits + Free vs Premium Comparison -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <span class="text-primary fw-bold text-uppercase small">Why Teams Choose Doctix</span>
                <h2 class="fw-bold mb-3">Fair Freemium Allowances & Enterprise Power</h2>
                <p class="text-secondary mb-4">
                    Unlike restrictive platforms that lock you out after a single action, Doctix gives every registered Free user <strong>4 uses per individual tool every calendar day</strong>. Need unlimited volume and 250 MB+ files? Upgrade to Premium in seconds.
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <i class="bi bi-check2-circle text-success fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Per-Tool Independent Daily Allowance</h6>
                            <p class="text-secondary small mb-0">Use Merge PDF 4 times AND Compress PDF 4 times AND PDF to Word 4 times on the exact same day.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="bi bi-shield-lock text-primary fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Bank-Grade File Privacy & Ownership Guards</h6>
                            <p class="text-secondary small mb-0">Strict MIME validation, malware scanning, private storage isolation, and automatic 24-hour file deletion.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="bi bi-cpu text-warning fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">AI-Native Document Intelligence</h6>
                            <p class="text-secondary small mb-0">Summarize contracts, ask questions on PDFs, check grammar, and translate documents in one click.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="table-responsive rounded-4 border shadow-sm">
                    <table class="table table-hover align-middle mb-0 bg-white">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 ps-4">Platform Capability</th>
                                <th class="py-3 text-center">Free Plan</th>
                                <th class="py-3 text-center text-primary">Premium Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-medium">Daily Allowance Per Individual Tool</td>
                                <td class="text-center"><span class="badge bg-light text-dark border">4 uses / tool / day</span></td>
                                <td class="text-center"><span class="badge bg-success"><i class="bi bi-infinity me-1"></i>Unlimited</span></td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">Allowance Independence Across Tools</td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i> Yes (Separate per tool)</td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i> Unlimited All Tools</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">Maximum File Upload Size</td>
                                <td class="text-center">Up to 25 MB</td>
                                <td class="text-center fw-bold text-primary">Up to 250 MB – 500 MB</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">Processing Queue Priority</td>
                                <td class="text-center">Standard Queue</td>
                                <td class="text-center fw-semibold text-success">VIP Priority Queue</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">AI Document Summarizer & Q&A</td>
                                <td class="text-center">4 / day per AI tool</td>
                                <td class="text-center fw-semibold text-success">Unlimited AI Operations</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">Automated Tax Invoices & Billing History</td>
                                <td class="text-center">—</td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i> Included</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 11. Subscription Pricing Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Transparent Pricing</span>
            <h2 class="fw-bold">Choose the Plan That Fits Your Workflow</h2>
            <p class="text-secondary">Start free with 4 daily uses per tool, or unlock unlimited power anytime.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($plans as $plan)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 rounded-4 {{ $plan->is_featured ? 'border-primary border-2 shadow' : 'border shadow-sm' }}">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 class="fw-bold mb-0">{{ $plan->name }}</h4>
                                @if($plan->badge_text)
                                    <span class="badge {{ $plan->is_featured ? 'bg-primary' : 'bg-light text-dark border' }} rounded-pill px-3">{{ $plan->badge_text }}</span>
                                @endif
                            </div>
                            <p class="text-secondary small mb-4">{{ $plan->description }}</p>

                            <div class="mb-4">
                                <span class="display-5 fw-bolder">${{ number_format($plan->price, 0) }}</span>
                                <span class="text-muted">/{{ $plan->billing_interval === 'free' ? 'forever' : ($plan->billing_interval === 'annual' ? 'year' : 'month') }}</span>
                            </div>

                            <ul class="list-unstyled d-flex flex-column gap-2 mb-4 flex-grow-1">
                                @foreach($plan->features as $feat)
                                    <li class="d-flex align-items-start gap-2 small">
                                        @if($feat->is_included)
                                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                            <span>{{ $feat->feature_name }}</span>
                                        @else
                                            <i class="bi bi-dash-circle text-muted mt-1"></i>
                                            <span class="text-muted">{{ $feat->feature_name }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if($plan->billing_interval === 'free')
                                @auth
                                    <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary w-100 rounded-3 fw-semibold">Included in Your Account</a>
                                @else
                                    <a href="{{ route('register') }}" class="btn btn-outline-dark w-100 rounded-3 fw-semibold">Create Free Account</a>
                                @endauth
                            @else
                                <a href="{{ route('checkout.show', $plan->slug) }}" class="btn {{ $plan->is_featured ? 'btn-doctix' : 'btn-dark' }} w-100 rounded-3 fw-semibold">
                                    Upgrade to {{ $plan->name }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 12. Customer Testimonials -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Trusted Worldwide</span>
            <h2 class="fw-bold">What Professionals Say About Doctix</h2>
        </div>
        <div class="row g-4">
            @foreach($testimonials as $t)
                <div class="col-md-4">
                    <div class="p-4 rounded-4 border h-100 bg-light bg-opacity-50 d-flex flex-column">
                        <div class="text-warning mb-3">
                            @for($i = 0; $i < $t->rating; $i++) <i class="bi bi-star-fill"></i> @endfor
                        </div>
                        <p class="text-dark flex-grow-1 fst-italic">"{{ $t->quote }}"</p>
                        <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top">
                            <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                {{ $t->avatar_initials }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $t->author_name }}</h6>
                                <small class="text-secondary">{{ $t->author_role }}, {{ $t->company }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 13. Frequently Asked Questions -->
<section class="py-5">
    <div class="container" style="max-width: 860px;">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Got Questions?</span>
            <h2 class="fw-bold">Frequently Asked Questions</h2>
        </div>
        <div class="accordion rounded-4 shadow-sm overflow-hidden" id="homeFaqAccordion">
            @foreach($faqs as $idx => $faq)
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $idx === 0 ? '' : 'collapsed' }} fw-semibold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $faq->id }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="faq-{{ $faq->id }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" data-bs-parent="#homeFaqAccordion">
                        <div class="accordion-body text-secondary">
                            {{ $faq->answer }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 14. Blog and Educational Resources -->
@if($blogPosts->count())
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-primary fw-bold text-uppercase small">Knowledge Hub</span>
                <h2 class="fw-bold mb-0">Guides & Productivity Insights</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">All Articles</a>
        </div>
        <div class="row g-4">
            @foreach($blogPosts as $post)
                <div class="col-md-4">
                    <div class="card h-100 border rounded-4 shadow-sm">
                        <div class="card-body p-4 d-flex flex-column">
                            <span class="badge badge-soft-primary align-self-start mb-2">{{ $post->category?->name ?? 'Guide' }}</span>
                            <h5 class="fw-bold"><a href="{{ route('blog.show', $post->slug) }}" class="text-dark text-decoration-none">{{ $post->title }}</a></h5>
                            <p class="text-secondary small flex-grow-1">{{ $post->excerpt }}</p>
                            <div class="d-flex justify-content-between align-items-center small text-muted pt-3 border-top">
                                <span><i class="bi bi-clock me-1"></i>{{ $post->reading_time_minutes }} min read</span>
                                <a href="{{ route('blog.show', $post->slug) }}" class="fw-semibold text-primary text-decoration-none">Read More &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 15. Newsletter Subscription Section -->
<section class="py-5">
    <div class="container">
        <div class="p-5 rounded-4 text-white text-center shadow" style="background: linear-gradient(135deg, #0F172A 0%, #312E81 100%);">
            <h2 class="fw-bold mb-2">Stay Ahead with Doctix Product Updates</h2>
            <p class="text-light opacity-75 mb-4 mx-auto" style="max-width: 580px;">
                Join thousands of professionals receiving monthly tips on document automation, AI workflows, and new tool releases.
            </p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="d-flex flex-column flex-sm-row gap-2 justify-content-center mx-auto" style="max-width: 500px;">
                @csrf
                <input type="email" name="email" required class="form-control form-control-lg rounded-3" placeholder="Enter your work email...">
                <button type="submit" class="btn btn-doctix btn-lg px-4 flex-shrink-0">Subscribe</button>
            </form>
        </div>
    </div>
</section>
@endsection

