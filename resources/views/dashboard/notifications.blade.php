@extends('layouts.dashboard')
@section('title', 'Notifications — Doctix')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bolder mb-0">System & Quota Notifications</h3>
    <form action="{{ route('dashboard.notifications.read') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-primary">Mark All as Read</button>
    </form>
</div>

<div class="card-modern p-4">
    @forelse($notifications as $n)
        <div class="d-flex justify-content-between align-items-start border-bottom py-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-{{ $n->type === 'warning' ? 'warning text-dark' : ($n->type === 'danger' ? 'danger' : 'primary') }}">{{ ucfirst($n->type) }}</span>
                    <strong class="{{ $n->is_read ? 'text-secondary' : 'text-dark' }}">{{ $n->title }}</strong>
                </div>
                <p class="text-secondary small mb-1">{{ $n->message }}</p>
                <small class="text-muted">{{ $n->created_at?->diffForHumans() }}</small>
            </div>
            @if($n->action_url)
                <a href="{{ $n->action_url }}" class="btn btn-sm btn-light border">Open</a>
            @endif
        </div>
    @empty
        <p class="text-muted mb-0">No notifications yet.</p>
    @endforelse
</div>
@endsection

