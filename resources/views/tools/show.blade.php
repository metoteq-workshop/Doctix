@extends('layouts.app')

@section('title', $tool->seo_title ?: ($tool->name . ' Online — Doctix'))
@section('meta_description', $tool->seo_description ?: $tool->short_description)
@section('meta_keywords', $tool->seo_keywords ?: strtolower($tool->name))

@section('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebApplication",
  "name": "{{ $tool->name }} — Doctix",
  "url": "{{ route('tools.show', $tool->slug) }}",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "All",
  "description": "{{ $tool->short_description }}"
}
</script>
@endsection

@section('content')
<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tools.index', ['category' => $tool->category?->slug]) }}" class="text-decoration-none">{{ $tool->category?->name ?? 'Tools' }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $tool->name }}</li>
        </ol>
    </nav>

    <!-- Tool Header & Quota Status Banner -->
    <div class="row g-4 align-items-center mb-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="tool-icon-box shadow-sm" style="background-color: {{ $tool->accent_color }}15; color: {{ $tool->accent_color }};">
                    <i class="bi {{ $tool->icon }}"></i>
                </div>
                <div>
                    <span class="badge bg-light text-secondary border mb-1">{{ $tool->category?->name }}</span>
                    <h1 class="h2 fw-bolder mb-0">{{ $tool->name }}</h1>
                </div>
            </div>
            <p class="text-secondary mb-0">{{ $tool->description }}</p>
        </div>

        <div class="col-lg-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-semibold text-uppercase text-muted">Today's Tool Allowance</span>
                    @if($allowance['is_unlimited'])
                        <span class="badge bg-success"><i class="bi bi-infinity me-1"></i>Unlimited Premium</span>
                    @elseif($allowance['allowed'])
                        <span class="badge badge-soft-primary" id="quotaBadge">{{ $allowance['used_today'] }} / {{ $allowance['daily_limit'] }} Used Today</span>
                    @else
                        <span class="badge bg-danger" id="quotaBadge">Limit Reached ({{ $allowance['used_today'] }}/{{ $allowance['daily_limit'] }})</span>
                    @endif
                </div>

                @if(!$allowance['is_unlimited'])
                    @php
                        $pct = $allowance['daily_limit'] > 0 ? min(100, round(($allowance['used_today'] / $allowance['daily_limit']) * 100)) : 100;
                    @endphp
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar {{ $pct >= 100 ? 'bg-danger' : 'bg-primary' }}" id="quotaProgressBar" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted">Max file size: <strong>{{ auth()->check() ? auth()->user()->maxFileSizeMb($tool) : $tool->free_max_file_size_mb }} MB</strong></span>
                        <a href="{{ route('pricing') }}" class="text-primary fw-semibold text-decoration-none">Go Unlimited &rarr;</a>
                    </div>
                @else
                    <div class="small text-secondary">
                        Priority processing &bull; Max upload: <strong>{{ auth()->user()->maxFileSizeMb($tool) }} MB</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Interactive Workspace Card -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border rounded-4 shadow-sm overflow-hidden">
                <div class="card-body p-4 p-lg-5">

                    <!-- Upgrade Prompt Banner if Daily Limit Reached -->
                    <div id="upgradePromptBox" class="alert alert-warning rounded-4 border-warning p-4 mb-4 {{ !$allowance['allowed'] ? '' : 'd-none' }}">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-stars fs-2 text-warning"></i>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Daily Free Allowance Reached for {{ $tool->name }}</h5>
                                <p class="text-secondary small mb-3" id="upgradePromptReason">
                                    {{ $allowance['reason'] ?? "You have used all {$tool->daily_free_limit} free daily operations for {$tool->name} today. Your allowance resets at midnight, or you can upgrade to Premium right now for unlimited usage!" }}
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('pricing') }}" class="btn btn-doctix btn-sm"><i class="bi bi-gem me-1"></i>Upgrade to Unlimited Premium</a>
                                    <a href="{{ route('tools.index') }}" class="btn btn-outline-dark btn-sm">Use Another Free Tool</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Server-Rendered Result Box (for non-AJAX fallback) -->
                    @if(session('processed_file_token'))
                        <div class="alert alert-success rounded-4 p-4 mb-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-2"></i>Processing Complete!</h5>
                                    <p class="small mb-0">File: <strong>{{ session('processed_file_name') }}</strong> ({{ session('processed_file_size') }})</p>
                                </div>
                                <a href="{{ route('files.download', session('processed_file_token')) }}" class="btn btn-success fw-semibold px-4 py-2 rounded-3">
                                    <i class="bi bi-download me-2"></i>Download Result
                                </a>
                            </div>
                            @if(session('preview_text'))
                                <hr>
                                <label class="small fw-bold text-uppercase text-muted mb-1">Output Preview:</label>
                                <pre class="bg-white p-3 rounded-3 border small mb-0" style="max-height: 240px; overflow-y: auto; white-space: pre-wrap;">{{ session('preview_text') }}</pre>
                            @endif
                        </div>
                    @endif

                    <!-- AJAX Dynamic Result Box -->
                    <div id="ajaxResultBox" class="alert alert-success rounded-4 p-4 mb-4 d-none">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h5 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-2"></i><span id="ajaxResultMessage">Processing Complete!</span></h5>
                                <p class="small mb-0">Output File: <strong id="ajaxFileName"></strong> (<span id="ajaxFileSize"></span>)</p>
                            </div>
                            <a href="#" id="ajaxDownloadBtn" class="btn btn-success fw-semibold px-4 py-2 rounded-3">
                                <i class="bi bi-download me-2"></i>Download Processed File
                            </a>
                        </div>
                        <div id="ajaxPreviewContainer" class="mt-3 d-none">
                            <label class="small fw-bold text-uppercase text-muted mb-1">Output Preview:</label>
                            <pre id="ajaxPreviewText" class="bg-white p-3 rounded-3 border small mb-0" style="max-height: 240px; overflow-y: auto; white-space: pre-wrap;"></pre>
                        </div>
                    </div>

                    <!-- Tool Processing Form -->
                    <form id="toolProcessForm" action="{{ route('tools.process', $tool->slug) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="idempotency_key" id="idempotencyKeyInput" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                        @if(in_array($tool->input_mode, ['file', 'multi_file', 'form_or_file']))
                            <div class="dropzone-area p-4 p-lg-5 text-center mb-4" id="dropzoneBox" onclick="document.getElementById('fileInput').click()">
                                <div class="mb-3">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width:68px;height:68px;font-size:2rem;">
                                        <i class="bi bi-cloud-arrow-up-fill"></i>
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-1">Drag & Drop your file{{ $tool->input_mode === 'multi_file' ? 's' : '' }} here</h5>
                                <p class="text-secondary small mb-3">
                                    Supported formats: <strong>{{ strtoupper(implode(', ', $tool->accepted_formats ?? ['PDF'])) }}</strong>
                                    &bull; Max size: <strong>{{ auth()->check() ? auth()->user()->maxFileSizeMb($tool) : $tool->free_max_file_size_mb }} MB</strong>
                                </p>
                                <button type="button" class="btn btn-outline-primary rounded-3 px-4 fw-semibold" onclick="event.stopPropagation(); document.getElementById('fileInput').click();">
                                    <i class="bi bi-folder2-open me-2"></i>Select File{{ $tool->input_mode === 'multi_file' ? 's' : '' }} from Device
                                </button>
                                <input type="file"
                                       id="fileInput"
                                       name="{{ $tool->input_mode === 'multi_file' ? 'files[]' : 'file' }}"
                                       class="d-none"
                                       {{ $tool->input_mode === 'multi_file' ? 'multiple' : '' }}
                                       accept="{{ $tool->acceptedExtensionsString() }}">
                                <div id="selectedFilesList" class="mt-3 text-start small"></div>
                            </div>
                        @endif

                        <!-- Tool-Specific Configuration Options -->
                        @if(!empty($tool->options_schema))
                            <div class="bg-light rounded-4 p-4 border mb-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-sliders me-2 text-primary"></i>Tool Configuration Options</h6>
                                <div class="row g-3">
                                    @foreach($tool->options_schema as $opt)
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">{{ $opt['label'] }}</label>
                                            @if(($opt['type'] ?? 'text') === 'select')
                                                <select name="{{ $opt['name'] }}" class="form-select">
                                                    @foreach(($opt['options'] ?? []) as $val => $label)
                                                        <option value="{{ $val }}" {{ ($opt['default'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif(($opt['type'] ?? 'text') === 'textarea')
                                                <textarea name="{{ $opt['name'] }}" rows="4" class="form-control" placeholder="{{ $opt['placeholder'] ?? '' }}">{{ $opt['default'] ?? '' }}</textarea>
                                            @else
                                                <input type="{{ $opt['type'] ?? 'text' }}"
                                                       name="{{ $opt['name'] }}"
                                                       value="{{ $opt['default'] ?? '' }}"
                                                       placeholder="{{ $opt['placeholder'] ?? '' }}"
                                                       class="form-control">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Live Processing Progress Indicator -->
                        <div id="processingProgressBox" class="mb-4 d-none">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span><span class="spinner-border spinner-border-sm text-primary me-2"></span>Processing with {{ $tool->name }} Engine...</span>
                                <span id="progressPercentText">65%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 75%"></div>
                            </div>
                        </div>

                        <button type="submit" id="submitProcessBtn" class="btn btn-doctix btn-lg w-100 py-3" {{ !$allowance['allowed'] ? 'disabled' : '' }}>
                            <i class="bi bi-lightning-charge-fill me-2"></i>Process with {{ $tool->name }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Step-by-Step Instructions & FAQs -->
            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="card border rounded-4 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-list-ol text-primary me-2"></i>How to Use {{ $tool->name }}</h5>
                            <ol class="text-secondary small d-flex flex-column gap-2 mb-0 ps-3">
                                @foreach(($tool->instructions ?? []) as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border rounded-4 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i>Security & Quota Guarantees</h5>
                            <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-0">
                                <li><i class="bi bi-check2 text-success me-2"></i><strong>4 Free Daily Uses:</strong> Independent counter for {{ $tool->name }}.</li>
                                <li><i class="bi bi-check2 text-success me-2"></i><strong>Zero Failure Penalty:</strong> Quota only counts when processing completes.</li>
                                <li><i class="bi bi-check2 text-success me-2"></i><strong>Private Storage:</strong> Signed download link accessible only to you.</li>
                                <li><i class="bi bi-check2 text-success me-2"></i><strong>Output Format:</strong> Produces verified <code>.{{ $tool->output_format }}</code> output.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($tool->faqs))
                <div class="card border rounded-4 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Frequently Asked Questions — {{ $tool->name }}</h5>
                        <div class="accordion" id="toolFaqAcc">
                            @foreach($tool->faqs as $i => $f)
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-semibold small" type="button" data-bs-toggle="collapse" data-bs-target="#tfaq-{{ $i }}">
                                            {{ $f['q'] }}
                                        </button>
                                    </h2>
                                    <div id="tfaq-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#toolFaqAcc">
                                        <div class="accordion-body small text-secondary">{{ $f['a'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar: Related Tools & Upgrade Card -->
        <div class="col-lg-4">
            <div class="card border rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Related {{ $tool->category?->name }}</h6>
                    <div class="d-flex flex-column gap-3">
                        @foreach($relatedTools as $rel)
                            <a href="{{ route('tools.show', $rel->slug) }}" class="d-flex align-items-center gap-3 text-decoration-none text-dark p-2 rounded-3 border bg-light bg-opacity-50">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:{{ $rel->accent_color }}15;color:{{ $rel->accent_color }}">
                                    <i class="bi {{ $rel->icon }}"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold small text-truncate">{{ $rel->name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">4 free uses/day &bull; .{{ $rel->output_format }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-4 text-white" style="background: linear-gradient(135deg, #1E1B4B 0%, #4338CA 100%);">
                <span class="badge bg-warning text-dark mb-2">Doctix Pro</span>
                <h5 class="fw-bold">Need Unlimited Daily Runs?</h5>
                <p class="small text-light opacity-75 mb-3">
                    Remove the 4-uses-per-tool daily cap, upload files up to 250 MB, and unlock priority cloud queues across all 60+ tools.
                </p>
                <a href="{{ route('pricing') }}" class="btn btn-light fw-bold w-100 rounded-3">Compare Premium Plans</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    const dropzone = document.getElementById('dropzoneBox');
    const filesList = document.getElementById('selectedFilesList');
    const form = document.getElementById('toolProcessForm');
    const progressBox = document.getElementById('processingProgressBox');
    const submitBtn = document.getElementById('submitProcessBtn');
    const ajaxResultBox = document.getElementById('ajaxResultBox');
    const upgradePromptBox = document.getElementById('upgradePromptBox');

    function renderSelectedFiles(files) {
        if (!filesList || !files || !files.length) return;
        let html = '<div class="list-group">';
        for (let i = 0; i < files.length; i++) {
            const sizeKb = (files[i].size / 1024).toFixed(1);
            html += `<div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span><i class="bi bi-file-earmark-check text-primary me-2"></i>${files[i].name}</span>
                        <span class="badge bg-light text-dark border">${sizeKb} KB</span>
                     </div>`;
        }
        html += '</div>';
        filesList.innerHTML = html;
    }

    if (fileInput) {
        fileInput.addEventListener('change', () => renderSelectedFiles(fileInput.files));
    }

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); dropzone.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('dragover'); });
        });
        dropzone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                renderSelectedFiles(fileInput.files);
            }
        });
    }

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            progressBox.classList.remove('d-none');
            ajaxResultBox.classList.add('d-none');
            submitBtn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                progressBox.classList.add('d-none');

                // Generate fresh idempotency key for subsequent operation
                document.getElementById('idempotencyKeyInput').value = crypto.randomUUID ? crypto.randomUUID() : ('idemp-' + Date.now() + '-' + Math.random());

                if (response.ok && data.success) {
                    ajaxResultBox.classList.remove('d-none');
                    document.getElementById('ajaxResultMessage').textContent = data.message;
                    document.getElementById('ajaxFileName').textContent = data.file_name;
                    document.getElementById('ajaxFileSize').textContent = data.file_size;
                    document.getElementById('ajaxDownloadBtn').href = data.download_url;

                    if (data.preview_text) {
                        document.getElementById('ajaxPreviewContainer').classList.remove('d-none');
                        document.getElementById('ajaxPreviewText').textContent = data.preview_text;
                    }

                    if (data.allowance && !data.allowance.is_unlimited) {
                        const badge = document.getElementById('quotaBadge');
                        const bar = document.getElementById('quotaProgressBar');
                        if (badge) badge.textContent = `${data.allowance.used_today} / ${data.allowance.daily_limit} Used Today`;
                        if (bar) bar.style.width = Math.min(100, Math.round((data.allowance.used_today / data.allowance.daily_limit) * 100)) + '%';

                        if (!data.allowance.allowed) {
                            upgradePromptBox.classList.remove('d-none');
                            submitBtn.disabled = true;
                            return;
                        }
                    }
                    submitBtn.disabled = false;
                } else {
                    if (data.upgrade_required) {
                        upgradePromptBox.classList.remove('d-none');
                        document.getElementById('upgradePromptReason').textContent = data.message;
                    } else {
                        alert(data.message || 'An error occurred while processing your file.');
                        submitBtn.disabled = false;
                    }
                }
            } catch (err) {
                progressBox.classList.add('d-none');
                submitBtn.disabled = false;
                form.submit(); // Fallback to standard submission
            }
        });
    }
});
</script>
@endpush

