@extends('layouts.admin')
@section('header_title', 'Modular Tools & Processing Engine Configuration')
@section('content')
<div class="card-modern p-4 mb-4">
    <h6 class="fw-bold mb-3">Add New Tool to Platform</h6>
    <form action="{{ route('admin.tools.store') }}" method="POST" class="row g-2">
        @csrf
        <div class="col-md-3">
            <label class="form-label small">Tool Name</label>
            <input type="text" name="name" required class="form-control form-control-sm" placeholder="e.g. Flatten PDF">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Category</label>
            <select name="tool_category_id" class="form-select form-select-sm">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Engine Driver</label>
            <select name="engine_driver" class="form-select form-select-sm">
                <option value="pdf">PDF Engine</option>
                <option value="image">Image Engine</option>
                <option value="document">Document Engine</option>
                <option value="ai">AI Engine</option>
                <option value="utility">Utility Engine</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Handler Action</label>
            <input type="text" name="handler_action" value="compress_pdf" required class="form-control form-control-sm">
        </div>
        <div class="col-md-1">
            <label class="form-label small">Daily Free</label>
            <input type="number" name="daily_free_limit" value="4" required class="form-control form-control-sm">
        </div>
        <div class="col-md-1">
            <label class="form-label small">Out Ext</label>
            <input type="text" name="output_format" value="pdf" required class="form-control form-control-sm">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <input type="hidden" name="input_mode" value="file">
            <input type="hidden" name="accepted_formats" value="pdf">
            <input type="hidden" name="free_max_file_size_mb" value="25">
            <input type="hidden" name="premium_max_file_size_mb" value="250">
            <button class="btn btn-sm btn-primary w-100">Add</button>
        </div>
        <div class="col-12">
            <input type="text" name="short_description" required class="form-control form-control-sm" placeholder="SEO-friendly short description of the tool...">
        </div>
    </form>
</div>

<div class="card-modern p-4">
    <div class="table-responsive">
        <table class="table align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tool Name</th>
                    <th>Category</th>
                    <th>Engine / Action</th>
                    <th>Daily Free Limit</th>
                    <th>File Limits (Free/Pro)</th>
                    <th>Total Uses</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tools as $t)
                    <tr>
                        <td>
                            <a href="{{ route('tools.show', $t->slug) }}" class="fw-bold text-dark text-decoration-none">{{ $t->name }}</a>
                            <div class="text-muted" style="font-size:0.75rem;">/{{ $t->slug }}</div>
                        </td>
                        <td>{{ $t->category?->name }}</td>
                        <td><code>{{ $t->engine_driver }}:{{ $t->handler_action }}</code></td>
                        <td class="fw-bold">{{ $t->daily_free_limit }} / day</td>
                        <td>{{ $t->free_max_file_size_mb }} MB / {{ $t->premium_max_file_size_mb }} MB</td>
                        <td>{{ number_format($t->total_usage_count) }}</td>
                        <td>
                            <span class="badge bg-{{ $t->is_active ? 'success' : 'secondary' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.tools.toggle', $t->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">{{ $t->is_active ? 'Disable' : 'Enable' }}</button>
                                </form>
                                <form action="{{ route('admin.tools.delete', $t->id) }}" method="POST" onsubmit="return confirm('Delete tool?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $tools->links() }}</div>
</div>
@endsection

