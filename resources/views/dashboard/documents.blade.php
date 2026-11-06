@extends('layouts.dashboard')
@section('title', 'My Processed Documents — Doctix')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bolder mb-1">My Processed Documents</h3>
        <p class="text-secondary small mb-0">Securely download your processed outputs. Files are automatically purged after 24 hours.</p>
    </div>
    <a href="{{ route('tools.index') }}" class="btn btn-primary rounded-3"><i class="bi bi-plus-lg me-1"></i>Process New File</a>
</div>

<div class="card-modern p-4">
    @if($files->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-folder2-open display-4 text-muted"></i>
            <h5 class="fw-bold mt-3">No processed documents yet</h5>
            <p class="text-secondary small">Upload and process any file using our 60+ tools to see it listed here.</p>
            <a href="{{ route('tools.index') }}" class="btn btn-primary">Explore Tools</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>File Name</th>
                        <th>Tool Used</th>
                        <th>Format</th>
                        <th>Size</th>
                        <th>Downloads</th>
                        <th>Processed At</th>
                        <th>Expires</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                        <tr>
                            <td class="fw-semibold">{{ $file->file_name }}</td>
                            <td>{{ $file->tool?->name ?? 'Tool' }}</td>
                            <td><span class="badge bg-light text-dark border text-uppercase">{{ $file->extension }}</span></td>
                            <td>{{ $file->formattedSize() }}</td>
                            <td>{{ $file->download_count }}</td>
                            <td class="small text-muted">{{ $file->created_at?->format('M j, Y H:i') }}</td>
                            <td class="small text-muted">{{ $file->expires_at?->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('files.download', $file->download_token) }}" class="btn btn-sm btn-success rounded-3">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $files->links() }}</div>
    @endif
</div>
@endsection

