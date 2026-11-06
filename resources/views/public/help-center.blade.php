@extends('layouts.app')
@section('title', 'Help Center & Documentation — Doctix')
@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bolder">Doctix Help Center</h1>
        <p class="text-secondary">Find quick answers about daily free allowances, file security, and subscription billing.</p>
    </div>
    <div class="row g-4">
        @foreach($faqs as $category => $items)
            <div class="col-md-6">
                <div class="card border rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-primary">{{ $category }}</h5>
                        @foreach($items as $faq)
                            <div class="mb-3 pb-3 border-bottom last-border-0">
                                <h6 class="fw-semibold mb-1">{{ $faq->question }}</h6>
                                <p class="text-secondary small mb-0">{{ $faq->answer }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

