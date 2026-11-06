@extends('layouts.admin')
@section('header_title', 'Subscription Plans & Active Memberships')
@section('content')
<div class="row g-4 mb-4">
    @foreach($plans as $plan)
        <div class="col-md-4">
            <div class="card-modern p-4 h-100">
                <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <input type="text" name="name" value="{{ $plan->name }}" class="form-control form-control-sm fw-bold me-2">
                        <span class="badge bg-primary">{{ $plan->slug }}</span>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small text-muted">Price</label>
                            <input type="number" step="0.01" name="price" value="{{ $plan->price }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted">Currency</label>
                            <input type="text" name="currency" value="{{ $plan->currency }}" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-4">
                            <label class="small text-muted">Interval</label>
                            <select name="billing_interval" class="form-select form-select-sm">
                                <option value="free" {{ $plan->billing_interval === 'free' ? 'selected' : '' }}>Free</option>
                                <option value="monthly" {{ $plan->billing_interval === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="annual" {{ $plan->billing_interval === 'annual' ? 'selected' : '' }}>Annual</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="small text-muted">Days</label>
                            <input type="number" name="interval_days" value="{{ $plan->interval_days }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <label class="small text-muted">Max MB</label>
                            <input type="number" name="max_file_size_mb" value="{{ $plan->max_file_size_mb }}" class="form-control form-control-sm">
                        </div>
                    </div>
                    <input type="hidden" name="daily_tool_limit" value="{{ $plan->daily_tool_limit }}">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_unlimited" value="1" {{ $plan->is_unlimited ? 'checked' : '' }}>
                        <label class="form-check-label small">Unlimited Daily Tool Access</label>
                    </div>
                    <button class="btn btn-sm btn-primary w-100">Save Plan Settings</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

<div class="card-modern p-4">
    <h6 class="fw-bold mb-3">Customer Subscriptions</h6>
    <div class="table-responsive">
        <table class="table align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>UUID</th>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Expires At</th>
                    <th>Extend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscriptions as $sub)
                    <tr>
                        <td><code>{{ \Illuminate\Support\Str::limit($sub->subscription_uuid, 12) }}</code></td>
                        <td>{{ $sub->user?->email }}</td>
                        <td class="fw-bold">{{ $sub->plan?->name }}</td>
                        <td class="text-uppercase">{{ $sub->payment_gateway }}</td>
                        <td><span class="badge bg-{{ $sub->status === 'active' ? 'success' : 'secondary' }}">{{ $sub->status }}</span></td>
                        <td>{{ $sub->ends_at?->format('M j, Y') }}</td>
                        <td>
                            <form action="{{ route('admin.subscriptions.extend', $sub) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                <input type="number" name="days" value="30" class="form-control form-control-sm" style="width:70px;">
                                <button class="btn btn-sm btn-outline-success">+Days</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

