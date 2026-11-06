@props(['tool', 'usedToday' => 0, 'isUnlimited' => false])

@php
    $limit = $tool->daily_free_limit ?: 4;
    $remaining = max(0, $limit - $usedToday);
@endphp

<a href="{{ route('tools.show', $tool->slug) }}" class="tool-card p-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div class="tool-icon-box" style="background-color: {{ $tool->accent_color }}15; color: {{ $tool->accent_color }};">
            <i class="bi {{ $tool->icon }}"></i>
        </div>
        <div class="d-flex gap-1 flex-wrap justify-content-end">
            @if($tool->is_new)
                <span class="badge bg-info text-dark rounded-pill">New</span>
            @endif
            @if($tool->is_popular)
                <span class="badge badge-soft-primary rounded-pill">Popular</span>
            @endif
            @if($tool->is_premium_only)
                <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-star-fill me-1"></i>Pro</span>
            @endif
        </div>
    </div>

    <h5 class="fw-bold mb-2 text-dark">{{ $tool->name }}</h5>
    <p class="text-secondary small flex-grow-1 mb-3">{{ $tool->short_description }}</p>

    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
        <span class="small text-muted">{{ $tool->category?->name ?? 'Tool' }}</span>
        @auth
            @if($isUnlimited)
                <span class="badge badge-soft-success"><i class="bi bi-infinity me-1"></i>Unlimited</span>
            @elseif($remaining > 0)
                <span class="badge bg-light text-dark border">{{ $remaining }}/{{ $limit }} free today</span>
            @else
                <span class="badge badge-soft-warning">Limit Reached ({{ $limit }}/{{ $limit }})</span>
            @endif
        @else
            <span class="badge bg-light text-secondary border">4/day Free</span>
        @endauth
    </div>
</a>

