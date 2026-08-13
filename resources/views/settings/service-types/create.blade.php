@extends('layouts.app')

@section('title', 'Add Service Type — Settings')

@section('body-class', 'settings-page')

@push('styles')
<style>
    .settings-page .main-content-area {
        background-color: #f8f9fc !important;
    }

    .settings-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.08);
        border: 1px solid #eef0f4;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .settings-card .card-header {
        background: #ffffff;
        border-bottom: 1px solid #eef0f4;
        padding: 14px 20px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-card .card-header i {
        color: #6c5ce7;
    }

    .settings-card .card-body {
        padding: 20px;
    }

    .settings-card .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        letter-spacing: 0.2px;
    }

    .settings-card .form-control,
    .settings-card .form-select {
        font-size: 0.875rem;
        border-radius: 8px;
        border-color: #e2e8f0;
    }

    .settings-card .btn-primary {
        background-color: #6c5ce7;
        border-color: #6c5ce7;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .settings-card .btn-outline-secondary {
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .field-required::after {
        content: " *";
        color: #dc2626;
    }

    .key-preview {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.78rem;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #eef0f4;
        border-radius: 6px;
        padding: 2px 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-plus me-2 text-secondary"></i>Add Service Type</h3>
            <p class="text-muted mb-0 small">New services appear in the booking form and appointment create page immediately.</p>
        </div>
        <a href="{{ route('settings.service-types.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="settings-card" style="max-width:640px;">
        <div class="card-header">
            <i class="fas fa-tools"></i> Service Details
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.service-types.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label field-required">Service Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. CERAMIC COATING" required>
                    <div class="form-text small text-muted">Shown in the booking form, create page and reports.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label field-required">Key</label>
                    <input type="text" class="form-control" name="key" value="{{ old('key') }}" placeholder="e.g. ceramic_coating" required>
                    <div class="form-text small text-muted">Unique lowercase identifier (letters, numbers, underscores). Stored on records — don't change it later.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" class="form-control" name="icon" value="{{ old('icon') }}" placeholder="e.g. 🛡️" maxlength="10">
                    <div class="form-text small text-muted">Optional emoji shown next to the service.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', \App\Models\ServiceType::max('sort_order') + 1) }}" min="0">
                    <div class="form-text small text-muted">Lower numbers appear first.</div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">
                            <strong>Active</strong> — offered in the booking form
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Service Type
                    </button>
                    <a href="{{ route('settings.service-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
