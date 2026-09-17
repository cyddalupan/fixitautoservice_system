@extends('layouts.app')

@section('title', 'Corrective Actions')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Corrective Actions</h1>
            <p class="text-muted">Track corrective actions from audits and NCRs</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quality-control.corrective-actions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Corrective Action
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('quality-control.corrective-actions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Action Type</label>
                    <select name="action_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($actionTypes as $type)
                            <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assigned To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Anyone</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('quality-control.corrective-actions.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Corrective Actions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Corrective Actions ({{ $correctiveActions->total() }})</h6>
        </div>
        <div class="card-body">
            @if($correctiveActions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Action #</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Assigned To</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($correctiveActions as $action)
                                <tr>
                                    <td>{{ $action->action_number ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('quality-control.corrective-actions.show', $action->id) }}" class="fw-bold text-decoration-none">
                                            {{ $action->title }}
                                        </a>
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $action->action_type ?? 'N/A')) }}</td>
                                    <td>{{ $action->assignee->name ?? 'N/A' }}</td>
                                    <td>{{ $action->due_date ? \Carbon\Carbon::parse($action->due_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        @php
                                            $stColor = match($action->status) {
                                                'completed', 'verified' => 'success',
                                                'in_progress' => 'warning',
                                                default => 'danger',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $stColor }}">{{ ucfirst(str_replace('_', ' ', $action->status ?? 'open')) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('quality-control.corrective-actions.show', $action->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $correctiveActions->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No corrective actions found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
