@extends('layouts.app')

@section('title', 'Edit Vehicle Brand')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Edit Brand: {{ $vehicleBrand->name }}</h1>
        <a href="{{ route('vehicle-brands.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('vehicle-brands.update', $vehicleBrand) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $vehicleBrand->name) }}" required maxlength="50">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="country_of_origin" class="form-label">Country of Origin</label>
                    <input type="text" name="country_of_origin" id="country_of_origin" class="form-control @error('country_of_origin') is-invalid @enderror" value="{{ old('country_of_origin', $vehicleBrand->country_of_origin) }}" maxlength="100">
                    @error('country_of_origin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" maxlength="500">{{ old('description', $vehicleBrand->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $vehicleBrand->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Brand
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
