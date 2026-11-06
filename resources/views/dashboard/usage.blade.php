@extends('layouts.dashboard')
@section('title', 'Daily Usage & Tool Allowances — Doctix')
@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h3 class="fw-bolder mb-1">Daily Usage & Allowance Tracker</h3>
        <p class="text-secondary small mb-0">
            Date: <strong>{{ $usageSummary['date'] }}</strong> ({{ $usageSummary['timezone'] }}) &bull; Resets automatically at midnight.
        </p>
    </div>
    @if(!$usageSummary['is_unlimited'])
        <a href="{{ route('pricing') }}" class="btn btn-primary rounded-3 fw-semibold mt-2 mt-md-0">
            <i class="bi bi-gem me-1"></i>Unlock Unlimited Usage
        </a>
    @endif
</div>

<div class="card-modern p-4 mb-4">
    <h6 class="fw-bold mb-3">7-Day Processing Volume</h6>
    <canvas id="userUsageChart" height="75"></canvas>
</div>

<div class="card-modern p-4">
    <h5 class="fw-bold mb-3">Per-Tool Daily Usage Breakdown</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tool</th>
                    <th>Category</th>
                    <th>Usage Today</th>
                    <th>Daily Limit</th>
                    <th>Remaining</th>
                    <th style="width: 180px;">Allowance Bar</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usageSummary['rows'] as $row)
                    <tr>
                        <td class="fw-semibold">
                            <i class="bi {{ $row['tool']->icon }} text-primary me-2"></i>{{ $row['tool']->name }}
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $row['tool']->category?->name }}</span></td>
                        <td class="fw-bold">{{ $row['used_today'] }}</td>
                        <td>{{ $row['daily_limit'] }}</td>
                        <td>{{ $row['remaining'] }}</td>
                        <td>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $row['is_limit_reached'] ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $row['percentage'] }}%"></div>
                            </div>
                        </td>
                        <td>
                            @if($row['is_limit_reached'])
                                <span class="badge bg-danger">Limit Reached</span>
                            @else
                                <span class="badge bg-success">Available</span>
                            @endif
                        </td>
                        <td>
                            @if($row['is_limit_reached'])
                                <a href="{{ route('pricing') }}" class="btn btn-sm btn-warning fw-semibold">Upgrade</a>
                            @else
                                <a href="{{ route('tools.show', $row['tool']->slug) }}" class="btn btn-sm btn-outline-primary">Open Tool</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('userUsageChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($dailyHistory)) !!},
        datasets: [{
            label: 'Completed Operations',
            data: {!! json_encode(array_values($dailyHistory)) !!},
            backgroundColor: '#4F46E5',
            borderRadius: 6
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endpush

