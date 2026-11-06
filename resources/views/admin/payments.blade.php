@extends('layouts.admin')
@section('header_title', 'Payment Transactions & Refund Management')
@section('content')
<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Gateway</th>
                    <th>Amount</th>
                    <th>Webhook Verified</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                    <tr>
                        <td class="fw-bold">{{ $p->transaction_reference }}</td>
                        <td>{{ $p->user?->email }}</td>
                        <td>{{ $p->plan?->name }}</td>
                        <td class="text-uppercase">{{ $p->gateway }}</td>
                        <td class="fw-bold">${{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                        <td>
                            @if($p->webhook_verified)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $p->status === 'succeeded' ? 'success' : ($p->status === 'refunded' ? 'warning text-dark' : 'danger') }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $p->created_at?->format('M j, Y H:i') }}</td>
                        <td>
                            @if($p->status === 'succeeded')
                                <form action="{{ route('admin.payments.refund', $p) }}" method="POST" onsubmit="return confirm('Issue full refund and revoke subscription?');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Issue Refund</button>
                                </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
</div>
@endsection

