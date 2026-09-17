@extends('layouts.app')

@section('title', 'Quality Control Checklists')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Quality Control Checklists</h1>
            <p class="text-muted">Manage inspection checklists for quality assurance</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quality-control.checklists.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Checklist
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('quality-control.checklists.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Service Type</label>
                    <select name="service_type" class="form-select">
                        <option value="">All Service Types</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type['value'] }}" {{ request('service_type') == $type['value'] ? 'selected' : '' }}>
                                {{ $type['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('quality-control.checklists.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Checklists Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Checklists ({{ $checklists->total() }})</h6>
        </div>
        <div class="card-body">
            @if($checklists->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Service Type</th>
                                <th>Items</th>
                                <th>Passing Score</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checklists as $checklist)
                                <tr>
                                    <td>
                                        <a href="{{ route('quality-control.checklists.show', $checklist->id) }}" class="fw-bold text-decoration-none">
                                            {{ $checklist->name }}
                                        </a>
                                    </td>
                                    <td>{{ str_replace('_', ' ', ucfirst($checklist->service_type ?? 'N/A')) }}</td>
                                    <td>{{ is_array($checklist->items) ? count($checklist->items) : 0 }}</td>
                                    <td>{{ $checklist->passing_score ?? 'N/A' }}%</td>
                                    <td>v{{ $checklist->version ?? 1 }}</td>
                                    <td>
                                        @if($checklist->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('quality-control.checklists.edit', $checklist->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('quality-control.checklists.show', $checklist->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $checklists->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No checklists found.</p>
                    <a href="{{ route('quality-control.checklists.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create your first checklist
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
