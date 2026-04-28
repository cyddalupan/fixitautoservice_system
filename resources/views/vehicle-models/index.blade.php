@extends('layouts.app')

@section('title', 'Vehicle Models')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Vehicle Models</h1>
        <div>
            <button type="button" class="btn btn-success me-2" onclick="syncVehicleData()" title="Sync all brands, models, and colors to the public quotation/customer forms">
                <i class="fas fa-sync"></i> Sync to Form
            </button>
            <a href="{{ route('vehicle-models.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Model
            </a>
        </div>
    </div>

    <div id="syncResult" class="d-none"></div>

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
@push('scripts')
<script>
function syncVehicleData() {
    const btn = event.currentTarget;
    const resultDiv = document.getElementById('syncResult');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    resultDiv.className = 'd-none';

    fetch('{{ route('vehicle-data.sync') }}')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                resultDiv.role = 'alert';
                resultDiv.innerHTML = '<strong>✓ Synced!</strong> ' + data.message +
                    '<br><small>Brands: ' + data.data.counts.brands +
                    ' | Models: ' + data.data.counts.models +
                    ' | Colors: ' + data.data.counts.colors +
                    ' | Synced at: ' + new Date(data.data.synced_at).toLocaleString() + '</small>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            } else {
                resultDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                resultDiv.role = 'alert';
                resultDiv.innerHTML = '✗ Sync failed: ' + data.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            }
        })
        .catch(err => {
            resultDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
            resultDiv.role = 'alert';
            resultDiv.innerHTML = '✗ Network error: ' + err.message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync"></i> Sync to Form';
        });
}
</script>
@endpush
@endsection
