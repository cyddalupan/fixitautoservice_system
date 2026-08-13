@extends('layouts.app')

@section('title', 'Service Types — Settings')

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

    .service-type-icon {
        font-size: 1.1rem;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f0ff;
        border-radius: 8px;
    }

    .st-key {
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
            <h3 class="mb-1"><i class="fas fa-tools me-2 text-secondary"></i>Service Types</h3>
            <p class="text-muted mb-0 small">Manage the services shown in the booking form and appointment create page.</p>
        </div>
        <a href="{{ route('settings.service-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Service Type
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="settings-card">
        <div class="card-header">
            <i class="fas fa-list"></i> Services List
            <span class="badge bg-soft-primary text-primary ms-auto">{{ $serviceTypes->count() }} services</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="text-muted small">
                        <tr>
                            <th style="width:50px;">Icon</th>
                            <th>Service</th>
                            <th>Key</th>
                            <th style="width:90px;">Order</th>
                            <th style="width:90px;">Status</th>
                            <th style="width:170px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviceTypes as $type)
                        <tr>
                            <td>
                                <span class="service-type-icon">{{ $type->icon ?: '🔧' }}</span>
                            </td>
                            <td class="fw-semibold">{{ $type->name }}</td>
                            <td><span class="st-key">{{ $type->key }}</span></td>
                            <td>{{ $type->sort_order }}</td>
                            <td>
                                @if($type->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('settings.service-types.edit', $type) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('settings.service-types.toggle', $type) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle active">
                                            <i class="fas {{ $type->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.service-types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('Delete {{ $type->name }}? Existing records keep their value but it will no longer be offered.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No service types yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
