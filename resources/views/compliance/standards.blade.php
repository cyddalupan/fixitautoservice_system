@extends('layouts.app')

@section('title', 'Compliance Standards')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Compliance Standards</h1>
            <p class="text-muted">Manage regulatory and internal compliance standards</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('compliance.standards.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Standard
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Standards ({{ $standards->total() }})</h6>
        </div>
        <div class="card-body">
            @if($standards->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standards as $standard)
                                <tr>
                                    <td>
                                        <a href="{{ route('compliance.standards.show', $standard->id) }}" class="fw-bold text-decoration-none">
                                            {{ $standard->name }}
                                        </a>
                                    </td>
                                    <td>{{ $standard->category ?? 'N/A' }}</td>
                                    <td>v{{ $standard->version ?? 1 }}</td>
                                    <td>
                                        @if($standard->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Archived</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('compliance.standards.edit', $standard->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('compliance.standards.show', $standard->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">{{ $standards->links() }}</div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No standards found.</p>
                    <a href="{{ route('compliance.standards.create') }}" class="btn btn-primary">Create your first standard</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
