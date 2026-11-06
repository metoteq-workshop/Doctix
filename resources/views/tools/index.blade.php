@extends('layouts.app')

@section('title', 'All Online PDF, Image, Document & AI Tools Directory | Doctix')
@section('meta_description', 'Browse and search 60+ online PDF, Image, Office Document, AI, and Productivity tools. Use every individual tool 4 times daily for free.')

@section('content')
<div class="bg-white border-bottom py-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-2">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active">Tools Directory</li>
            </ol>
        </nav>
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="fw-bolder mb-2">Complete Online Tools Directory</h1>
                <p class="text-secondary mb-0">
                    Access {{ $tools->count() }} specialized tools. Free users enjoy <strong>4 completed operations per tool every day</strong> — or upgrade to Premium for unlimited access.
                </p>
            </div>
            <div class="col-lg-5">
                <form action="{{ route('tools.index') }}" method="GET" class="d-flex gap-2">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search tools by name or format...">
                        <button class="btn btn-doctix" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Category & Status Filters -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('tools.index') }}" class="btn btn-sm rounded-pill {{ !request('category') ? 'btn-dark' : 'btn-outline-secondary' }}">
                    All Categories
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('tools.index', ['category' => $cat->slug, 'q' => request('q')]) }}"
                       class="btn btn-sm rounded-pill {{ request('category') === $cat->slug ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="bi {{ $cat->icon }} me-1"></i>{{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('tools.index', array_merge(request()->query(), ['filter' => 'popular'])) }}" class="btn btn-sm {{ request('filter') === 'popular' ? 'btn-primary' : 'btn-light border' }}">Popular</a>
                <a href="{{ route('tools.index', array_merge(request()->query(), ['filter' => 'featured'])) }}" class="btn btn-sm {{ request('filter') === 'featured' ? 'btn-primary' : 'btn-light border' }}">Featured</a>
                <a href="{{ route('tools.index', array_merge(request()->query(), ['filter' => 'new'])) }}" class="btn btn-sm {{ request('filter') === 'new' ? 'btn-primary' : 'btn-light border' }}">Recently Added</a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    @if($tools->isEmpty())
        <div class="text-center py-5 bg-white rounded-4 border">
            <i class="bi bi-search display-4 text-muted"></i>
            <h4 class="fw-bold mt-3">No matching tools found</h4>
            <p class="text-secondary">Try clearing your search filter or browsing all categories.</p>
            <a href="{{ route('tools.index') }}" class="btn btn-doctix">Reset Filters</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($tools as $tool)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <x-tool-card :tool="$tool" :usedToday="$usageCounts[$tool->id] ?? 0" :isUnlimited="$isUnlimited" />
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

