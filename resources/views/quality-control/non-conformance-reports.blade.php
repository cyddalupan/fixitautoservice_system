@extends('layouts.app')

@section('title', 'Non-Conformance Reports')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Non-Conformance Reports</h1>
            <p class="text-muted">Track quality issues and their resolution</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('quality-control.ncrs.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New NCR
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('quality-control.ncrs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All Severities</option>
                        <option value="minor" {{ request('severity') == 'minor' ? 'selected' : '' }}>Minor</option>
                        <option value="major" {{ request('severity') == 'major' ? 'selected' : '' }}>Major</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Technician</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ request('assigned_to') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('quality-control.ncrs.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- NCR Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">NCRs ({{ $ncrs->total() }})</h6>
        </div>
        <div class="card-body">
            @if($ncrs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>NCR #</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Reported</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ncrs as $ncr)
                                <tr>
                                    <td>{{ $ncr->ncr_number ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('quality-control.ncrs.show', $ncr->id) }}" class="fw-bold text-decoration-none">
                                            {{ $ncr->title }}
                                        </a>
                                    </td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $ncr->type ?? 'N/A')) }}</td>
                                    <td>
                                        @php
                                            $sevColor = match($ncr->severity) {
                                                'critical' => 'danger',
                                                'major' => 'warning',
                                                default => 'info',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $sevColor }}">{{ ucfirst($ncr->severity ?? 'N/A') }}</span>
                                    </td>
                                    <td>{{ $ncr->reported_date ? \Carbon\Carbon::parse($ncr->reported_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $ncr->due_date ? \Carbon\Carbon::parse($ncr->due_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        @php
                                            $stColor = match($ncr->status) {
                                                'resolved', 'closed' => 'success',
                                                'in_progress' => 'warning',
                                                default => 'danger',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $stColor }}">{{ ucfirst(str_replace('_', ' ', $ncr->status ?? 'open')) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('quality-control.ncrs.show', $ncr->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $ncrs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No non-conformance reports found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
