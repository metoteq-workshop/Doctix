@extends('layouts.admin')
@section('header_title', 'Security Audit Logs, Activity Feed & System Health')
@section('content')
<div class="card-modern p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex flex-wrap gap-3 small">
            <span><strong>PHP:</strong> {{ $systemHealth['php_version'] }}</span>
            <span><strong>Laravel:</strong> {{ $systemHealth['laravel_version'] }}</span>
            <span><strong>Database:</strong> {{ $systemHealth['db_driver'] }}</span>
            <span><strong>Timezone:</strong> {{ $systemHealth['timezone'] }}</span>
            <span><strong>Storage Writable:</strong> <span class="badge bg-{{ $systemHealth['storage_writable'] ? 'success' : 'danger' }}">{{ $systemHealth['storage_writable'] ? 'Yes' : 'No' }}</span></span>
        </div>
        <form action="{{ route('admin.maintenance.run') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-broom me-1"></i>Purge Expired Temp Files Now</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">Security & Administrative Audit Logs</h6>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Actor</th>
                            <th>Severity</th>
                            <th>IP</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $log->module }}</span></td>
                                <td class="fw-semibold">{{ $log->action }}</td>
                                <td>{{ $log->actor?->email ?? 'System' }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->severity === 'critical' ? 'danger' : ($log->severity === 'warning' ? 'warning text-dark' : 'info text-dark') }}">
                                        {{ $log->severity }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $log->ip_address }}</td>
                                <td class="text-muted">{{ $log->created_at?->format('M j, H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $auditLogs->links() }}</div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">Live User Activity Stream</h6>
            <ul class="list-group list-group-flush small">
                @foreach($activityLogs as $act)
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <div>
                            <strong>{{ $act->user?->name ?? 'Guest' }}:</strong> {{ $act->description }}
                        </div>
                        <span class="text-muted ms-2 flex-shrink-0">{{ $act->created_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

