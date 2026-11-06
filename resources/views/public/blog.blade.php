@extends('layouts.app')
@section('title', 'Blog & Document Productivity Guides — Doctix')
@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bolder">Doctix Knowledge Hub & Blog</h1>
        <p class="text-secondary">Tutorials, security insights, and productivity guides for modern document workflows.</p>
    </div>

    <div class="row g-4">
        @foreach($posts as $post)
            <div class="col-md-4">
                <div class="card h-100 border rounded-4 shadow-sm">
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="badge badge-soft-primary align-self-start mb-2">{{ $post->category?->name }}</span>
                        <h5 class="fw-bold"><a href="{{ route('blog.show', $post->slug) }}" class="text-dark text-decoration-none">{{ $post->title }}</a></h5>
                        <p class="text-secondary small flex-grow-1">{{ $post->excerpt }}</p>
                        <div class="d-flex justify-content-between align-items-center small text-muted pt-3 border-top">
                            <span>{{ $post->published_at?->format('M j, Y') }}</span>
                            <a href="{{ route('blog.show', $post->slug) }}" class="fw-semibold text-primary text-decoration-none">Read Article &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

