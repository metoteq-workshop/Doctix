@extends('layouts.dashboard')
@section('title', 'Support Tickets — Doctix')
@section('content')
<h3 class="fw-bolder mb-4">Help Desk & Support Tickets</h3>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Open a New Ticket</h5>
            <form action="{{ route('dashboard.tickets.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Subject</label>
                    <input type="text" name="subject" required class="form-control" placeholder="Brief summary of issue">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category" class="form-select">
                            <option value="technical">Technical / Tool</option>
                            <option value="billing">Billing & Plans</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea name="message" rows="4" required class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Submit Ticket</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Your Support Tickets</h5>
            @forelse($tickets as $t)
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">#{{ $t->ticket_number }} — {{ $t->subject }}</span>
                        <span class="badge bg-{{ $t->status === 'answered' ? 'success' : 'primary' }}">{{ ucfirst($t->status) }}</span>
                    </div>
                    <p class="small text-secondary mb-2">{{ $t->message }}</p>
                    @foreach($t->replies as $r)
                        <div class="p-2 rounded bg-light small mb-2">
                            <strong>{{ $r->is_staff_reply ? 'Doctix Support' : 'You' }}:</strong> {{ $r->message }}
                        </div>
                    @endforeach
                    <form action="{{ route('dashboard.tickets.reply', $t) }}" method="POST" class="d-flex gap-2 mt-2">
                        @csrf
                        <input type="text" name="message" required class="form-control form-control-sm" placeholder="Write a reply...">
                        <button class="btn btn-sm btn-outline-primary">Reply</button>
                    </form>
                </div>
            @empty
                <p class="text-muted small mb-0">You have not opened any support tickets yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

