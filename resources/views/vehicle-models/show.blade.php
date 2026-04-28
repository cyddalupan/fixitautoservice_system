@extends('layouts.app')

@section('title', 'Vehicle Model Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Model: {{ $vehicleModel->name }}</h1>
        <div>
            <a href="{{ route('vehicle-models.edit', $vehicleModel) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('vehicle-models.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Brand</th><td>{{ $vehicleModel->brand->name }}</td></tr>
                <tr><th>Model Name</th><td>{{ $vehicleModel->name }}</td></tr>
                <tr><th>Slug</th><td>{{ $vehicleModel->slug }}</td></tr>
                <tr><th>Year Start</th><td>{{ $vehicleModel->year_start ?? '—' }}</td></tr>
                <tr><th>Year End</th><td>{{ $vehicleModel->year_end ?? '—' }}</td></tr>
                <tr><th>Body Type</th><td>{{ $vehicleModel->body_type ?? '—' }}</td></tr>
                <tr><th>Vehicle Type</th><td>{{ $vehicleModel->vehicle_type ?? '—' }}</td></tr>
                <tr><th>Status</th>
                    <td>
                        @if($vehicleModel->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                </tr>
                <tr><th>Created</th><td>{{ $vehicleModel->created_at->format('Y-m-d H:i') }}</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection
