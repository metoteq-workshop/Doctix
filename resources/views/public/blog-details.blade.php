@extends('layouts.app')
@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description ?: $post->excerpt)
@section('content')
<div class="container py-5" style="max-width: 820px;">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
            <li class="breadcrumb-item active">{{ $post->title }}</li>
        </ol>
    </nav>
    <span class="badge badge-soft-primary mb-2">{{ $post->category?->name }}</span>
    <h1 class="fw-bolder mb-3">{{ $post->title }}</h1>
    <div class="text-muted small mb-4">
        By {{ $post->author?->name ?? 'Doctix Editorial' }} &bull; {{ $post->published_at?->format('F j, Y') }} &bull; {{ $post->reading_time_minutes }} min read
    </div>
    <div class="bg-white p-4 p-lg-5 rounded-4 border shadow-sm lh-lg">
        {!! $post->content !!}
    </div>
</div>
@endsection

