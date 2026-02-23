@extends('layouts.app')

@section('title', 'Service Requests - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">
                                <i class="fas fa-tools me-2"></i>Service Requests
                            </h3>
                            <p class="card-subtitle text-muted mb-0">Manage your service requests and track their status</p>
                        </div>
                        <div>
                            <a href="{{ route('portal.service-requests.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Service Request
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Pending</h6>
                                            <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                    <small>Awaiting review</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">In Progress</h6>
                                            <h2 class="mb-0">{{ $stats['in_progress'] }}</h2>
                                        </div>
                                        <i class="fas fa-cogs fa-2x opacity-50"></i>
                                    </div>
                                    <small>Being processed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Completed</h6>
                                            <h2 class="mb-0">{{ $stats['completed'] }}</h2>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                    </div>
                                    <small>Successfully resolved</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Total</h6>
                                            <h2 class="mb-0">{{ $stats['total'] }}</h2>
                                        </div>
                                        <i class="fas fa-list fa-2x opacity-50"></i>
                                    </div>
                                    <small>All requests</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter me-2"></i>Filter Requests
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('portal.service-requests.index') }}" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="priority" class="form-label">Priority</label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="">All Priorities</option>
                                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="vehicle_id" class="form-label">Vehicle</label>
                                        <select class="form-select" id="vehicle_id" name="vehicle_id">
                                            <option value="">All Vehicles</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_range" class="form-label">Date Range</label>
                                        <select class="form-select" id="date_range" name="date_range">
                                            <option value="">All Time</option>
                                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                            <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                            <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2" id="customDateRange" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="row g-3 mt-3">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="search" placeholder="Search requests..." value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary" type="submit">
                                                <i class="fas fa-search"></i> Search
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" onclick="resetFilters()">
                                                <i class="fas fa-times"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Service Requests Table -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>Service Requests ({{ $serviceRequests->total() }})
                                </h5>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">
                                        <i class="fas fa-download me-1"></i> Export CSV
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printTable()">
                                        <i class="fas fa-print me-1"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($serviceRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                                    </div>
                                                </th>
                                                <th>ID</th>
                                                <th>Subject</th>
                                                <th>Vehicle</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Created</th>
                                                <th>Updated</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($serviceRequests as $request)
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $request->id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>SR-{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <a href="{{ route('portal.service-requests.show', $request->id) }}" class="text-decoration-none">
                                                                    {{ $request->subject }}
                                                                </a>
                                                                @if($request->description)
                                                                    <small class="text-muted d-block mt-1">
                                                                        {{ Str::limit($request->description, 50) }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                            @if($request->attachments_count > 0)
                                                                <i class="fas fa-paperclip ms-2 text-muted" title="{{ $request->attachments_count }} attachment(s)"></i>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($request->vehicle)
                                                            <div class="vehicle-info">
                                                                <div class="fw-bold">{{ $request->vehicle->year }} {{ $request->vehicle->make }}</div>
                                                                <small class="text-muted">{{ $request->vehicle->model }}</small>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No vehicle</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($request->status === 'pending') bg-warning
                                                            @elseif($request->status === 'in_progress') bg-info
                                                            @elseif($request->status === 'completed') bg-success
                                                            @elseif($request->status === 'cancelled') bg-danger
                                                            @else bg-secondary @endif">
                                                            {{ ucfirst($request->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($request->priority === 'low') bg-secondary
                                                            @elseif($request->priority === 'medium') bg-primary
                                                            @elseif($request->priority === 'high') bg-warning
                                                            @elseif($request->priority === 'urgent') bg-danger
                                                            @else bg-light text-dark @endif">
                                                            {{ ucfirst($request->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <div>{{ $request->created_at->format('M j, Y') }}</div>
                                                            <div class="text-muted">{{ $request->created_at->format('g:i A') }}</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <div>{{ $request->updated_at->format('M j, Y') }}</div>
                                                            <div class="text-muted">{{ $request->updated_at->format('g:i A') }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('portal.service-requests.show', $request->id) }}" class="btn btn-outline-primary" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($request->status === 'pending')
                                                                <a href="{{ route('portal.service-requests.edit', $request->id) }}" class="btn btn-outline-secondary" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-outline-danger" onclick="cancelRequest({{ $request->id }})" title="Cancel">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Bulk Actions -->
                                <div class="card-footer bg-light" id="bulkActions" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span id="selectedCount">0</span> request(s) selected
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportSelected()">
                                                <i class="fas fa-download me-1"></i> Export Selected
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSelected()">
                                                <i class="fas fa-trash me-1"></i> Delete Selected
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pagination -->
                                @if($serviceRequests->hasPages())
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                Showing {{ $serviceRequests->firstItem() }} to {{ $serviceRequests->lastItem() }} of {{ $serviceRequests->total() }} requests
                                            </div>
                                            <div>
                                                {{ $serviceRequests->links() }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                                    <h4>No Service Requests Found</h4>
                                    <p class="text-muted mb-4">
                                        @if(request()->hasAny(['status', 'priority', 'vehicle_id', 'search', 'date_range']))
                                            Try adjusting your filters or search criteria
                                        @else
                                            You haven't created any service requests yet
                                        @endif
                                    </p>
                                    <a href="{{ route('portal.service-requests.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Create Your First Service Request
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-question-circle me-2"></i>About Service Requests
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-check-circle text-success me-2"></i>When to use Service Requests:</h6>
                                    <ul class="small">
                                        <li>Requesting non-urgent maintenance or repairs</li>
                                        <li>Scheduling future service appointments</li>
                                        <li>Getting estimates for complex repairs</li>
                                        <li>Requesting parts or accessories</li>
                                        <li>General service inquiries</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-clock text-warning me-2"></i>Response Times:</h6>
                                    <ul class="small">
                                        <li><strong>Urgent:</strong> Within 2 business hours</li>
                                        <li><strong>High:</strong> Within 4 business hours</li>
                                        <li><strong>Medium:</strong> Within 1 business day</li>
                                        <li><strong>Low:</strong> Within 2 business days</li>
                                    </ul>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-phone me-2"></i>
                                        <strong>Need immediate assistance?</strong> Call us at (555) 123-4567 for urgent matters.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Show/hide custom