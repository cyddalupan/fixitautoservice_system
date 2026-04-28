@extends('layouts.app')

@section('title', 'Edit Service: ' . $serviceItem->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Service: {{ $serviceItem->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('service-items.update', $serviceItem) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $serviceItem->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="retail_price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control @error('retail_price') is-invalid @enderror" 
                                           id="retail_price" name="retail_price" 
                                           value="{{ old('retail_price', $serviceItem->retail_price) }}" required>
                                </div>
                                @error('retail_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description', $serviceItem->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="estimated_duration_minutes" class="form-label">Estimated Duration (minutes)</label>
                                <input type="number" min="1" class="form-control @error('estimated_duration_minutes') is-invalid @enderror" 
                                       id="estimated_duration_minutes" name="estimated_duration_minutes" 
                                       value="{{ old('estimated_duration_minutes', $serviceItem->estimated_duration_minutes) }}">
                                @error('estimated_duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty if not applicable</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category ID</label>
                                <input type="number" class="form-control @error('category_id') is-invalid @enderror" 
                                       id="category_id" name="category_id" 
                                       value="{{ old('category_id', $serviceItem->category_id) }}">
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">For future category system</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Internal Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="2">{{ old('notes', $serviceItem->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Internal notes only, not shown to customers</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $serviceItem->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (available for selection)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('service-items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Services
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Service
                                </button>
                                <a href="{{ route('service-items.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection