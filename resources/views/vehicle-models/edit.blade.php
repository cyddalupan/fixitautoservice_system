@extends('layouts.app')

@section('title', 'Edit Vehicle Model')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Edit Model: {{ $vehicleModel->name }}</h1>
        <a href="{{ route('vehicle-models.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('vehicle-models.update', $vehicleModel) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="vehicle_brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                    <select name="vehicle_brand_id" id="vehicle_brand_id" class="form-select @error('vehicle_brand_id') is-invalid @enderror" required>
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('vehicle_brand_id', $vehicleModel->vehicle_brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Model Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $vehicleModel->name) }}" required maxlength="50">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="year_start" class="form-label">Year Start</label>
                        <input type="number" name="year_start" id="year_start" class="form-control @error('year_start') is-invalid @enderror" value="{{ old('year_start', $vehicleModel->year_start) }}" min="1900" max="{{ date('Y') + 1 }}">
                        @error('year_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="year_end" class="form-label">Year End</label>
                        <input type="number" name="year_end" id="year_end" class="form-control @error('year_end') is-invalid @enderror" value="{{ old('year_end', $vehicleModel->year_end) }}" min="1900" max="{{ date('Y') + 1 }}">
                        @error('year_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="body_type" class="form-label">Body Type</label>
                        <select name="body_type" id="body_type" class="form-select @error('body_type') is-invalid @enderror">
                            <option value="">Select Body Type</option>
                            <option value="Sedan" {{ old('body_type', $vehicleModel->body_type) == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                            <option value="SUV" {{ old('body_type', $vehicleModel->body_type) == 'SUV' ? 'selected' : '' }}>SUV</option>
                            <option value="Pickup" {{ old('body_type', $vehicleModel->body_type) == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                            <option value="Hatchback" {{ old('body_type', $vehicleModel->body_type) == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                            <option value="MPV" {{ old('body_type', $vehicleModel->body_type) == 'MPV' ? 'selected' : '' }}>MPV</option>
                            <option value="Van" {{ old('body_type', $vehicleModel->body_type) == 'Van' ? 'selected' : '' }}>Van</option>
                            <option value="Coupe" {{ old('body_type', $vehicleModel->body_type) == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                            <option value="Convertible" {{ old('body_type', $vehicleModel->body_type) == 'Convertible' ? 'selected' : '' }}>Convertible</option>
                            <option value="Wagon" {{ old('body_type', $vehicleModel->body_type) == 'Wagon' ? 'selected' : '' }}>Wagon</option>
                            <option value="Truck" {{ old('body_type', $vehicleModel->body_type) == 'Truck' ? 'selected' : '' }}>Truck</option>
                            <option value="Bus" {{ old('body_type', $vehicleModel->body_type) == 'Bus' ? 'selected' : '' }}>Bus</option>
                        </select>
                        @error('body_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror">
                            <option value="">Select Type</option>
                            <option value="Passenger" {{ old('vehicle_type', $vehicleModel->vehicle_type) == 'Passenger' ? 'selected' : '' }}>Passenger</option>
                            <option value="Commercial" {{ old('vehicle_type', $vehicleModel->vehicle_type) == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                            <option value="Luxury" {{ old('vehicle_type', $vehicleModel->vehicle_type) == 'Luxury' ? 'selected' : '' }}>Luxury</option>
                            <option value="Off-road" {{ old('vehicle_type', $vehicleModel->vehicle_type) == 'Off-road' ? 'selected' : '' }}>Off-road</option>
                            <option value="Sports" {{ old('vehicle_type', $vehicleModel->vehicle_type) == 'Sports' ? 'selected' : '' }}>Sports</option>
                        </select>
                        @error('vehicle_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $vehicleModel->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Model
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
