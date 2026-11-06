@extends('layouts.dashboard')
@section('title', 'Invoice ' . $invoice->invoice_number . ' — Doctix')
@section('content')
<div class="card-modern p-5 mx-auto" style="max-width: 780px;">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <h3 class="fw-bolder mb-1">TAX INVOICE</h3>
            <div class="text-muted small">Invoice Number: <strong>{{ $invoice->invoice_number }}</strong></div>
            <div class="text-muted small">Issued Date: {{ $invoice->issued_at?->format('F j, Y') }}</div>
        </div>
        <div class="text-end">
            <h4 class="fw-bold text-primary mb-0">Doctix Cloud Inc.</h4>
            <span class="badge bg-success text-uppercase">{{ $invoice->status }}</span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-sm-6">
            <div class="text-muted small text-uppercase fw-bold mb-1">Billed To</div>
            <div class="fw-bold">{{ $invoice->billing_name }}</div>
            <div class="text-secondary small">{{ $invoice->billing_email }}</div>
        </div>
        <div class="col-sm-6 text-sm-end">
            <div class="text-muted small text-uppercase fw-bold mb-1">Payment Reference</div>
            <div class="fw-semibold">{{ $invoice->payment?->transaction_reference ?? 'N/A' }}</div>
            <div class="text-secondary small text-uppercase">Gateway: {{ $invoice->payment?->gateway ?? 'Stripe' }}</div>
        </div>
    </div>

    <table class="table table-bordered align-middle mb-4">
        <thead class="table-light">
            <tr>
                <th>Description</th>
                <th class="text-end">Amount ({{ $invoice->currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $invoice->subscription?->plan?->name ?? 'Doctix Premium Subscription' }}</strong>
                    <div class="small text-muted">Unlimited access to all 60+ PDF, Image, Document & AI tools</div>
                </td>
                <td class="text-end">${{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount > 0)
                <tr>
                    <td class="text-success">Promotional Discount</td>
                    <td class="text-end text-success">-${{ number_format($invoice->discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="fw-bold">Total Paid</td>
                <td class="text-end fw-bolder fs-5">${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center">
        <span class="small text-muted">Thank you for subscribing to Doctix!</span>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer me-1"></i>Print / Save PDF</button>
    </div>
</div>
@endsection

