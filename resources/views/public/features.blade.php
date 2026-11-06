@extends('layouts.app')
@section('title', 'Platform Features & Enterprise Capabilities — Doctix')
@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <span class="badge badge-soft-primary px-3 py-2 rounded-pill mb-2">Platform Architecture</span>
        <h1 class="fw-bolder">Built for Speed, Accuracy & Security</h1>
        <p class="text-secondary mx-auto" style="max-width: 660px;">
            Explore how Doctix combines 60+ document tools with enterprise-grade security, AI intelligence, and fair daily usage enforcement.
        </p>
    </div>

    @foreach($categories as $cat)
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi {{ $cat->icon }} fs-4 text-primary"></i>
                <h3 class="fw-bold mb-0">{{ $cat->name }}</h3>
            </div>
            <p class="text-secondary mb-4">{{ $cat->description }}</p>
            <div class="row g-4">
                @foreach($cat->activeTools->take(4) as $tool)
                    <div class="col-sm-6 col-lg-3">
                        <x-tool-card :tool="$tool" />
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection

