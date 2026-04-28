@extends('layouts.app')

@section('title', $vehicleBrand->name)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-tag me-2"></i>{{ $vehicleBrand->name }}</h1>
            <p class="text-muted mb-0">{{ $vehicleBrand->country_of_origin ? "Origin: {$vehicleBrand->country_of_origin}" : '' }}</p>
        </div>
        <div>
            <a href="{{ route('vehicle-brands.edit', $vehicleBrand) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('vehicle-brands.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

@if($vehicleBrand->description)
<div class="alert alert-info">{{ $vehicleBrand->description }}</div>
@endif

<div class="card">
    <div class="card-header"><h5 class="mb-0">Models ({{ $vehicleBrand->models->count() }})</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Model</th>
                        <th>Year(s)</th>
                        <th>Type</th>
                        <th>Popularity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleBrand->models as $model)
                    <tr>
                        <td><strong>{{ $model->name }}</strong></td>
                        <td>
                            @if($model->year_start || $model->year_end)
                                {{ $model->year_start ?? '—' }} – {{ $model->year_end ?? '—' }}
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $model->vehicle_type ?? 'N/A' }}</span></td>
                        <td>{{ $model->popularity_score }}</td>
                        <td>
                            @if($model->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No models for this brand.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
