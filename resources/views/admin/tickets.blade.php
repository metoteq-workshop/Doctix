@extends('layouts.admin')
@section('header_title', 'Customer Support Tickets')
@section('content')
<div class="card-modern p-4">
    @forelse($tickets as $t)
        <div class="border rounded-3 p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="fw-bold">#{{ $t->ticket_number }} — {{ $t->subject }}</span>
                    <span class="text-muted small ms-2">From: {{ $t->name }} ({{ $t->email }})</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-{{ $t->status === 'closed' ? 'secondary' : 'primary' }}">{{ ucfirst($t->status) }}</span>
                    @if($t->status !== 'closed')
                        <form action="{{ route('admin.tickets.close', $t) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Close</button>
                        </form>
                    @endif
                </div>
            </div>
            <p class="small text-secondary mb-2">{{ $t->message }}</p>
            @foreach($t->replies as $r)
                <div class="p-2 rounded bg-light small mb-2">
                    <strong>{{ $r->is_staff_reply ? 'Staff' : 'Customer' }}:</strong> {{ $r->message }}
                </div>
            @endforeach
            @if($t->status !== 'closed')
                <form action="{{ route('admin.tickets.reply', $t) }}" method="POST" class="d-flex gap-2 mt-2">
                    @csrf
                    <input type="text" name="message" required class="form-control form-control-sm" placeholder="Send staff reply...">
                    <button class="btn btn-sm btn-primary">Send Reply</button>
                </form>
            @endif
        </div>
    @empty
        <p class="text-muted mb-0">No support tickets found.</p>
    @endforelse
</div>
@endsection

