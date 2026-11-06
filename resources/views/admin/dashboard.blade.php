@extends('layouts.admin')
@section('header_title', 'Executive SaaS Analytics & Control Center')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="text-secondary small fw-semibold">Registered Users</div>
            <div class="fs-3 fw-bolder">{{ number_format($totalUsers) }}</div>
            <div class="small text-muted">{{ $activeUsers }} active &bull; {{ $freeUsersCount }} free &bull; <strong class="text-success">{{ $premiumUsersCount }} premium</strong></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="text-secondary small fw-semibold">Recurring Revenue (MRR / ARR)</div>
            <div class="fs-3 fw-bolder text-success">${{ number_format($mrr, 2) }} <small class="fs-6 text-muted">/mo</small></div>
            <div class="small text-muted">ARR: <strong>${{ number_format($arr, 2) }}</strong> &bull; Total Paid: ${{ number_format($totalRevenue, 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="text-secondary small fw-semibold">Payment Transactions</div>
            <div class="fs-3 fw-bolder">{{ number_format($totalTransactions) }}</div>
            <div class="small"><span class="text-success">{{ $successfulPayments }} succeeded</span> &bull; <span class="text-danger">{{ $failedPayments }} failed</span></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="text-secondary small fw-semibold">Total Tool Operations</div>
            <div class="fs-3 fw-bolder text-primary">{{ number_format($totalToolUsage) }}</div>
            <div class="small text-muted">{{ $todayToolUsage }} operations executed today</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">7-Day Tool Processing Volume</h6>
            <canvas id="adminUsageChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">7-Day Subscription Revenue (USD)</h6>
            <canvas id="adminRevenueChart" height="110"></canvas>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Most Popular Tools</h6>
                <a href="{{ route('admin.tools') }}" class="small text-decoration-none">Manage Tools</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tool</th>
                            <th>Category</th>
                            <th>Daily Free Limit</th>
                            <th>Total Runs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($popularTools as $t)
                            <tr>
                                <td class="fw-semibold">{{ $t->name }}</td>
                                <td>{{ $t->category?->name }}</td>
                                <td>{{ $t->daily_free_limit }} / day</td>
                                <td class="fw-bold">{{ number_format($t->total_usage_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Payment Transactions</h6>
                <a href="{{ route('admin.payments') }}" class="small text-decoration-none">All Payments</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $tx)
                            <tr>
                                <td class="fw-semibold">{{ $tx->transaction_reference }}</td>
                                <td>{{ $tx->user?->email }}</td>
                                <td class="fw-bold">${{ number_format($tx->amount, 2) }}</td>
                                <td><span class="badge bg-{{ $tx->status === 'succeeded' ? 'success' : 'danger' }}">{{ $tx->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('adminUsageChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{ label: 'Operations', data: {!! json_encode($chartUsageSeries) !!}, borderColor: '#4F46E5', backgroundColor: 'rgba(79,70,229,0.1)', fill: true, tension: 0.3 }]
    }
});
new Chart(document.getElementById('adminRevenueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{ label: 'Revenue ($)', data: {!! json_encode($chartRevenueSeries) !!}, backgroundColor: '#10B981', borderRadius: 6 }]
    }
});
</script>
@endpush

