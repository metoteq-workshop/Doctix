@extends('layouts.admin')
@section('header_title', 'System Settings, Quota Limits, Payment Gateways & AI Credentials')
@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">Platform, Quota & AI Settings</h6>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Site Name</label>
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'Doctix' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Support Email</label>
                        <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@doctix.io' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Homepage Hero Headline</label>
                        <input type="text" name="hero_headline" value="{{ $settings['hero_headline'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Daily Free Limit / Tool</label>
                        <input type="number" name="daily_free_limit" value="{{ $settings['daily_free_limit'] ?? 4 }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Free Max Upload (MB)</label>
                        <input type="number" name="free_max_file_size_mb" value="{{ $settings['free_max_file_size_mb'] ?? 25 }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Premium Max Upload (MB)</label>
                        <input type="number" name="premium_max_file_size_mb" value="{{ $settings['premium_max_file_size_mb'] ?? 250 }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Temp File Retention (Hours)</label>
                        <input type="number" name="temp_file_retention_hours" value="{{ $settings['temp_file_retention_hours'] ?? 24 }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">AI Provider</label>
                        <select name="ai_provider" class="form-select form-select-sm">
                            <option value="openai">OpenAI (GPT-4o)</option>
                            <option value="gemini">Google Gemini</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">OpenAI / AI Provider API Key (Stored Encrypted)</label>
                        <input type="password" name="openai_api_key" placeholder="sk-..." class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Default SEO Meta Title</label>
                        <input type="text" name="meta_title" value="{{ $settings['meta_title'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100">Save System Configuration</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-modern p-4">
            <h6 class="fw-bold mb-3">Payment Gateway Integrations</h6>
            @foreach($gateways as $gw)
                <form action="{{ route('admin.settings.gateway.update', $gw) }}" method="POST" class="border rounded-3 p-3 mb-3">
                    @csrf @method('PUT')
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">{{ $gw->name }} (<code>{{ $gw->code }}</code>)</span>
                        <div class="d-flex gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ $gw->is_enabled ? 'checked' : '' }}>
                                <label class="form-check-label small">Enabled</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_sandbox" value="1" {{ $gw->is_sandbox ? 'checked' : '' }}>
                                <label class="form-check-label small">Sandbox</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-5">
                            <input type="text" name="public_key" value="{{ $gw->public_key }}" placeholder="Public Key" class="form-control form-control-sm">
                        </div>
                        <div class="col-5">
                            <input type="password" name="secret_key" placeholder="Update Secret Key (Encrypted)" class="form-control form-control-sm">
                        </div>
                        <div class="col-2">
                            <button class="btn btn-sm btn-outline-dark w-100">Save</button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection

