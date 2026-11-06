@extends('layouts.admin')
@section('header_title', 'User Management, Subscriptions & Daily Quota Reset')
@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card-modern p-4">
            <form action="{{ route('admin.users') }}" method="GET" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or email...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-2">Create New User</h6>
            <form action="{{ route('admin.users.store') }}" method="POST" class="row g-2">
                @csrf
                <div class="col-6"><input type="text" name="name" required placeholder="Full Name" class="form-control form-control-sm"></div>
                <div class="col-6"><input type="email" name="email" required placeholder="Email" class="form-control form-control-sm"></div>
                <div class="col-5"><input type="password" name="password" required placeholder="Password" class="form-control form-control-sm"></div>
                <div class="col-4">
                    <select name="role" class="form-select form-select-sm">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-3"><button class="btn btn-sm btn-dark w-100">Add</button></div>
            </form>
        </div>
    </div>
</div>

<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Entitlement</th>
                    <th>Assign Plan</th>
                    <th>Reset Today's Usage</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $u->name }}</div>
                            <div class="text-muted">{{ $u->email }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $u->role }}</span></td>
                        <td>
                            <span class="badge bg-{{ $u->status === 'active' ? 'success' : 'danger' }}">{{ $u->status }}</span>
                        </td>
                        <td>
                            @if($u->hasActivePremiumSubscription())
                                <span class="badge bg-success">Unlimited Premium</span>
                            @else
                                <span class="badge bg-light text-dark border">Free (4/tool/day)</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.users.assign-sub', $u) }}" method="POST" class="d-flex gap-1">
                                @csrf
                                <select name="subscription_plan_id" class="form-select form-select-sm" style="width:140px;">
                                    @foreach($plans as $pl)
                                        <option value="{{ $pl->id }}">{{ $pl->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-primary">Assign</button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.users.reset-usage', $u) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning text-dark"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Today's Quota</button>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.users.toggle', $u) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">{{ $u->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                                </form>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.delete', $u) }}" method="POST" onsubmit="return confirm('Delete user?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection

