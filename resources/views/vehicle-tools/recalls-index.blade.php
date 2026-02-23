@extends('layouts.app')

@section('title', 'Recall Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Recall Management</h1>
            <p class="text-muted">Manage vehicle safety recalls and customer notifications</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('vehicle-tools.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="{{ route('recalls.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Recall
                </a>
                <a href="{{ route('recalls.dashboard') }}" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Recalls</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRecalls) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Open</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($openRecalls) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($inProgressRecalls) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($completedRecalls) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Needs Notification</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($needsNotification) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Urgent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($urgentRecalls) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-filter"></i> Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('recalls.index') }}" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="open" {{ $status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="severity">Severity</label>
                            <select name="severity" id="severity" class="form-control">
                                <option value="all" {{ $severity == 'all' ? 'selected' : '' }}>All Severities</option>
                                <option value="low" {{ $severity == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $severity == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $severity == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ $severity == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_from">Date From</label>
                            <input type="date" 
                                   name="date_from" 
                                   id="date_from" 
                                   class="form-control"
                                   value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_to">Date To</label>
                            <input type="date" 
                                   name="date_to" 
                                   id="date_to" 
                                   class="form-control"
                                   value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="search">Search</label>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   class="form-control"
                                   placeholder="Search by VIN, component, customer..."
                                   value="{{ $search }}">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="{{ route('recalls.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recalls Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-triangle"></i> Recalls
                        <span class="badge badge-primary ml-2">{{ $recalls->total() }}</span>
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#batchCheckModal">
                            <i class="fas fa-search"></i> Batch Check
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#batchNotifyModal">
                            <i class="fas fa-bell"></i> Batch Notify
                        </button>
                        <a href="{{ route('recalls.export', ['format' => 'csv', 'status' => $status]) }}" 
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recalls->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Campaign #</th>
                                        <th>Vehicle</th>
                                        <th>Component</th>
                                        <th>Recall Date</th>
                                        <th>Status</th>
                                        <th>Severity</th>
                                        <th>Customer</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recalls as $recall)
                                    <tr class="{{ $recall->is_overdue ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $recall->campaign_number }}</strong>
                                            @if($recall->is_overdue)
                                                <br>
                                                <small class="text-danger">
                                                    <i class="fas fa-clock"></i> Overdue
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vehicle-tools.service-history', ['vehicleId' => $recall->vehicle_id]) }}" 
                                               class="text-decoration-none">
                                                {{ $recall->vehicle->year }} {{ $recall->vehicle->make }} {{ $recall->vehicle->model }}
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-barcode"></i> {{ $recall->vehicle->vin }}
                                            </small>
                                        </td>
                                        <td>
                                            <strong>{{ $recall->component }}</strong>
                                            @if($recall->summary)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($recall->summary, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $recall->recall_date->format('M d, Y') }}
                                            <br>
                                            <small class="text-muted">{{ $recall->recall_date->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $recall->status_color }}">
                                                {{ ucfirst($recall->status) }}
                                            </span>
                                            @if(!$recall->customer_notified && in_array($recall->status, ['open', 'in_progress']))
                                                <br>
                                                <small class="text-danger">
                                                    <i class="fas fa-bell-slash"></i> Not notified
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $recall->severity_color }}">
                                                {{ ucfirst($recall->severity) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($recall->vehicle->customer)
                                                <a href="{{ route('customers.show', $recall->vehicle->customer_id) }}" 
                                                   class="text-decoration-none">
                                                    {{ $recall->vehicle->customer->full_name }}
                                                </a>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> {{ $recall->vehicle->customer->phone }}
                                                </small>
                                            @else
                                                <span class="text-muted">No customer</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('recalls.show', $recall->id) }}" 
                                                   class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('recalls.edit', $recall->id) }}" 
                                                   class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(!$recall->customer_notified && in_array($recall->status, ['open', 'in_progress']))
                                                    <form action="{{ route('recalls.send-notification', $recall->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-outline-danger" 
                                                                title="Send Notification"
                                                                onclick="return confirm('Send recall notification to customer?')">
                                                            <i class="fas fa-bell"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                Showing {{ $recalls->firstItem() }} to {{ $recalls->lastItem() }} of {{ $recalls->total() }} recalls
                            </div>
                            <div>
                                {{ $recalls->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">No Recalls Found</h5>
                            <p class="text-muted">
                                @if($status != 'all' || $search || $dateFrom || $dateTo)
                                    Try adjusting your filters
                                @else
                                    No recalls have been recorded yet.
                                @endif
                            </p>
                            <a href="{{ route('recalls.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add First Recall
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-bell text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">Needs Notification</div>
                            <div class="h5">{{ $needsNotification }} recalls</div>
                            <a href="{{ route('recalls.needs-notification') }}" class="small">
                                View all <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <div class="icon-circle bg-danger">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">Overdue Recalls</div>
                            <div class="h5">{{ $overdueRecalls }} recalls</div>
                            <a href="{{ route('recalls.overdue') }}" class="small">
                                View all <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-left