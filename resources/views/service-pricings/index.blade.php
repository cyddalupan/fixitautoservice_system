@php
    $bodyClass = 'service-pricing-page';
@endphp
@extends('layouts.app')

@section('title', 'Service Pricing')

@section('body-class', $bodyClass)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-tags me-2"></i>Service Pricing</h4>
            <p class="text-muted mb-0 small">Manage pricing per service type, vehicle type, and variant</p>
        </div>
        <div>
            <a href="{{ route('service-pricings.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Add Pricing
            </a>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('service-pricings.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Service Type</label>
                    <select name="service_type_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Services</option>
                        @foreach($serviceTypes as $st)
                            <option value="{{ $st->id }}" {{ request('service_type_id') == $st->id ? 'selected' : '' }}>
                                {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Vehicle Type</label>
                    <select name="vehicle_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach($vehicleTypes as $vt)
                            <option value="{{ $vt }}" {{ request('vehicle_type') == $vt ? 'selected' : '' }}>
                                {{ ucfirst($vt) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <a href="{{ route('service-pricings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Pricing table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Service</th>
                            <th>Vehicle Type</th>
                            <th>Vehicle Model</th>
                            <th>Variant Label</th>
                            <th class="text-end">Price (₱)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pricings as $serviceName => $items)
                            @foreach($items as $pricing)
                                <tr>
                                    <td class="ps-4">
                                        @if($loop->first)
                                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $serviceName }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pricing->vehicle_type)
                                            <span class="badge bg-info bg-opacity-10 text-info text-uppercase">{{ $pricing->vehicle_type }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pricing->brand_name || $pricing->model_name)
                                            {{ $pricing->brand_name }} {{ $pricing->model_name }}
                                        @else
                                            <span class="text-muted">All Models</span>
                                        @endif
                                    </td>
                                    <td>{{ $pricing->variant_label ?: '—' }}</td>
                                    <td class="text-end fw-bold">₱{{ number_format($pricing->price, 2) }}</td>
                                    <td>
                                        @if($pricing->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('service-pricings.edit', $pricing) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('service-pricings.destroy', $pricing) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this pricing entry?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-tags fa-3x mb-3 d-block"></i>
                                    No pricing entries yet.
                                    <a href="{{ route('service-pricings.create') }}" class="d-block mt-2">Create your first pricing entry</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($pricings->isNotEmpty())
    <div class="mt-3">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Showing {{ $pricings->flatten()->count() }} pricing entries across {{ $pricings->count() }} service types.
        </small>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .service-pricing-page .table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
    }
    .service-pricing-page .table td {
        vertical-align: middle;
    }
    .service-pricing-page .badge {
        font-weight: 500;
    }
</style>
@endpush
