@php
    $bodyClass = 'service-pricing-form-page';
@endphp
@extends('layouts.app')

@section('title', 'Add Service Pricing')

@section('body-class', $bodyClass)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('service-pricings.index') }}" class="text-decoration-none text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Back to Service Pricing
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Add Service Pricing</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('service-pricings.store') }}">
                @csrf

                <div class="row g-3">
                    {{-- Service Type --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                        <select name="service_type_id" class="form-select @error('service_type_id') is-invalid @enderror" required>
                            <option value="">— Select Service —</option>
                            @foreach($serviceTypes as $st)
                                <option value="{{ $st->id }}" {{ old('service_type_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Vehicle Type --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror">
                            <option value="">— All Types —</option>
                            @foreach($vehicleTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('vehicle_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Leave blank if per-model</small>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Brand Name --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Brand</label>
                        <input type="text" name="brand_name" class="form-control @error('brand_name') is-invalid @enderror"
                               value="{{ old('brand_name') }}" placeholder="e.g. Toyota, Honda, Mitsubishi">
                        <small class="text-muted">Optional — manual input</small>
                        @error('brand_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Model Name --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Model</label>
                        <input type="text" name="model_name" class="form-control @error('model_name') is-invalid @enderror"
                               value="{{ old('model_name') }}" placeholder="e.g. Vios, Civic, Montero">
                        <small class="text-muted">Optional — manual input</small>
                        @error('model_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    {{-- Variant Label --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Variant Label</label>
                        <input type="text" name="variant_label" class="form-control @error('variant_label') is-invalid @enderror"
                               value="{{ old('variant_label') }}" placeholder="e.g. Single AC, 3L Oil, Standard">
                        <small class="text-muted">Describe the variant (e.g. oil liters, AC type)</small>
                        @error('variant_label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Price --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Price (₱) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0" name="price"
                                   class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price') }}" placeholder="0.00" required>
                        </div>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sort Order --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" min="0" name="sort_order"
                               class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', 0) }}">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Active --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes (optional)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
                                  placeholder="Any additional info about this pricing...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Pricing
                    </button>
                    <a href="{{ route('service-pricings.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
