@extends('layouts.dashboard')
@section('title', 'Workspace Overview — Doctix')
@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h3 class="fw-bolder mb-1">Welcome back, {{ $user->name }}!</h3>
        <p class="text-secondary small mb-0">
            Current Plan: <strong class="text-dark">{{ $currentPlan?->name ?? 'Free Plan' }}</strong>
            @if($activeSubscription)
                &bull; Status: <span class="badge bg-success">{{ ucfirst($activeSubscription->status) }}</span>
                &bull; Renews/Expires: <strong>{{ $activeSubscription->ends_at?->format('M j, Y') ?? 'Lifetime' }}</strong>
            @else
                &bull; Allowance: <span class="badge bg-primary">4 Free Uses / Individual Tool / Day</span>
            @endif
        </p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="{{ route('tools.index') }}" class="btn btn-primary rounded-3 fw-semibold"><i class="bi bi-grid-fill me-1"></i>Browse All Tools</a>
        @if(!$usageSummary['is_unlimited'])
            <a href="{{ route('pricing') }}" class="btn btn-dark rounded-3 fw-semibold"><i class="bi bi-gem me-1 text-warning"></i>Upgrade to Unlimited</a>
        @endif
    </div>
</div>

<!-- KPI Summary Widgets -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-semibold">Current Subscription</span>
                <i class="bi bi-award-fill text-primary fs-4"></i>
            </div>
            <h4 class="fw-bolder mb-1">{{ $currentPlan?->name ?? 'Free Plan' }}</h4>
            <span class="small {{ $usageSummary['is_unlimited'] ? 'text-success fw-semibold' : 'text-muted' }}">
                {{ $usageSummary['is_unlimited'] ? 'Unlimited Daily Access' : '4 uses / tool / day' }}
            </span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-semibold">Operations Today ({{ $usageSummary['date'] }})</span>
                <i class="bi bi-lightning-charge-fill text-warning fs-4"></i>
            </div>
            <h4 class="fw-bolder mb-1">{{ $usageSummary['total_operations_today'] }}</h4>
            <span class="small text-muted">Across {{ $usageSummary['tools_used_count_today'] }} distinct tool(s)</span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-semibold">Total All-Time Operations</span>
                <i class="bi bi-check2-all text-success fs-4"></i>
            </div>
            <h4 class="fw-bolder mb-1">{{ number_format($totalOperationsAllTime) }}</h4>
            <span class="small text-muted">Max file size: {{ $user->maxFileSizeMb() }} MB</span>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-semibold">Unread Notifications</span>
                <i class="bi bi-bell-fill text-info fs-4"></i>
            </div>
            <h4 class="fw-bolder mb-1">{{ $unreadNotifications }}</h4>
            <a href="{{ route('dashboard.notifications') }}" class="small text-decoration-none">View notifications &rarr;</a>
        </div>
    </div>
</div>

<!-- Daily Usage Allowance Snapshot + Recent Processed Files -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-modern p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0">Today's Tool Usage & Daily Allowances</h5>
                    <small class="text-muted">Each tool has its own independent 4-use daily allowance on the Free Plan.</small>
                </div>
                <a href="{{ route('dashboard.usage') }}" class="btn btn-sm btn-outline-primary rounded-3">Full Usage Table</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Tool</th>
                            <th>Usage Today</th>
                            <th>Daily Limit</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($usageSummary['rows'], 0, 8) as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('tools.show', $row['tool']->slug) }}" class="fw-semibold text-dark text-decoration-none">
                                        <i class="bi {{ $row['tool']->icon }} text-primary me-1"></i>{{ $row['tool']->name }}
                                    </a>
                                </td>
                                <td class="fw-bold">{{ $row['used_today'] }}</td>
                                <td>{{ $row['daily_limit'] }}</td>
                                <td style="width: 130px;">
                                    <div class="progress" style="height: 7px;">
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-modern p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Recent Processed Files</h5>
                <a href="{{ route('dashboard.documents') }}" class="small text-decoration-none">All Files</a>
            </div>
            @if($recentFiles->isEmpty())
                <p class="text-muted small mb-0">No processed documents yet. <a href="{{ route('tools.index') }}">Try a tool now!</a></p>
            @else
                <div class="list-group list-group-flush">
                    @foreach($recentFiles as $file)
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div class="overflow-hidden me-2">
                                <div class="fw-semibold small text-truncate">{{ $file->file_name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">{{ $file->tool?->name }} &bull; {{ $file->formattedSize() }}</div>
                            </div>
                            <a href="{{ route('files.download', $file->download_token) }}" class="btn btn-sm btn-outline-success rounded-3 flex-shrink-0">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card-modern p-4">
            <h5 class="fw-bold mb-3">Recent Account Activity</h5>
            @if($recentActivities->isEmpty())
                <p class="text-muted small mb-0">No recent activity logged.</p>
            @else
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    @foreach($recentActivities->take(5) as $act)
                        <li class="d-flex justify-content-between border-bottom pb-2">
                            <span>{{ $act->description }}</span>
                            <span class="text-muted flex-shrink-0 ms-2">{{ $act->created_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

