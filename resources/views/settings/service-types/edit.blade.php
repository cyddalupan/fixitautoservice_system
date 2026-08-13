@extends('layouts.app')

@section('title', 'Edit Service Type — Settings')

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
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-edit me-2 text-secondary"></i>Edit Service Type</h3>
            <p class="text-muted mb-0 small">Update how this service is shown across the platform.</p>
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
            <form method="POST" action="{{ route('settings.service-types.update', $serviceType) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label field-required">Service Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $serviceType->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label field-required">Key</label>
                    <input type="text" class="form-control" name="key" value="{{ old('key', $serviceType->key) }}" required>
                    <div class="form-text small text-muted">Unique lowercase identifier. Changing it does not rewrite existing records.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" class="form-control" name="icon" value="{{ old('icon', $serviceType->icon) }}" maxlength="10">
                </div>

                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $serviceType->sort_order) }}" min="0">
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $serviceType->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <strong>Active</strong> — offered in the booking form
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('settings.service-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
