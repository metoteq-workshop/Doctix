@extends('layouts.dashboard')
@section('title', 'Recent Activities & Login History — Doctix')
@section('content')
<h3 class="fw-bolder mb-4">Activity & Security History</h3>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Recent Platform Operations</h5>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Event</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $act)
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $act->event_type }}</span></td>
                                <td>{{ $act->description }}</td>
                                <td class="text-muted">{{ $act->ip_address }}</td>
                                <td class="text-muted">{{ $act->created_at?->format('M j, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $activities->links() }}</div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Recent Sign-In History</h5>
            <ul class="list-group list-group-flush small">
                @foreach($loginHistories as $login)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $login->device }} ({{ $login->ip_address }})</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ \Illuminate\Support\Str::limit($login->user_agent, 55) }}</div>
                        </div>
                        <span class="text-muted">{{ $login->logged_in_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

