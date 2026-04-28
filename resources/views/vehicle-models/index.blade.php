@extends('layouts.app')

@section('title', 'Vehicle Models')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Vehicle Models</h1>
        <a href="{{ route('vehicle-models.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Model
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label for="brand_id" class="form-label">Filter by Brand</label>
                        <select name="brand_id" id="brand_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('vehicle-models.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Model Name</th>
                            <th>Year Start</th>
                            <th>Year End</th>
                            <th>Body Type</th>
                            <th>Vehicle Type</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($models as $model)
                            <tr>
                                <td>{{ $model->brand->name }}</td>
                                <td>{{ $model->name }}</td>
                                <td>{{ $model->year_start ?? '—' }}</td>
                                <td>{{ $model->year_end ?? '—' }}</td>
                                <td>{{ $model->body_type ?? '—' }}</td>
                                <td>{{ $model->vehicle_type ?? '—' }}</td>
                                <td>
                                    @if($model->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vehicle-models.edit', $model) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('vehicle-models.destroy', $model) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this model?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No models found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
