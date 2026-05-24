@extends('layouts.app')

@section('content')
@include('partials.customer-process-assets')
<div class="container-fluid px-4 py-4">
    <!-- Status Header Bar -->
    <div class="status-header-bar d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-list me-2" style="color: var(--primary-color);"></i>
                Work Order #{{ $workOrder->work_order_number ?? $workOrder->id }}
            </h4>
            <span class="badge bg-{{ $workOrder->status_badge ?? 'secondary' }}">
                {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status ?? 'draft')) }}
            </span>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Customer Summary -->
    @if($workOrder->customer)
    <div class="customer-summary-card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div class="customer-avatar">
                    {{ strtoupper(substr($workOrder->customer->first_name, 0, 1)) }}{{ strtoupper(substr($workOrder->customer->last_name, 0, 1)) }}
                </div>
                <div class="flex-grow-1" style="min-width: 200px;">
                    <div class="customer-name">{{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}</div>
                    <div class="customer-subtitle">
                        <i class="fas fa-phone-alt me-1"></i> {{ $workOrder->customer->phone ?? 'No phone' }}
                        @if($workOrder->customer->email)
                            &nbsp;·&nbsp; <i class="fas fa-envelope me-1"></i>{{ $workOrder->customer->email }}
                        @endif
                    </div>
                </div>
                <div class="d-flex">
                    <div class="stat-item">
                        <div class="stat-value">{{ $workOrder->vehicle->year ?? '' }} {{ $workOrder->vehicle->make ?? '' }}</div>
                        <div class="stat-label">{{ $workOrder->vehicle->model ?? 'Vehicle' }}</div>
                    </div>
                    @if($workOrder->vehicle && $workOrder->vehicle->license_plate)
                    <div class="stat-item">
                        <div class="stat-value">{{ $workOrder->vehicle->license_plate }}</div>
                        <div class="stat-label">Plate</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section Navigation Tabs -->
    <ul class="nav nav-pills section-nav mb-4" id="detailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                <i class="fas fa-info-circle me-1"></i>Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="services-tab" data-bs-toggle="pill" data-bs-target="#services" type="button" role="tab">
                <i class="fas fa-tools me-1"></i>Services &amp; Items
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="techs-tab" data-bs-toggle="pill" data-bs-target="#techs" type="button" role="tab">
                <i class="fas fa-users me-1"></i>Technicians
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="timeline-tab" data-bs-toggle="pill" data-bs-target="#timeline" type="button" role="tab">
                <i class="fas fa-history me-1"></i>Timeline
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="related-tab" data-bs-toggle="pill" data-bs-target="#related" type="button" role="tab">
                <i class="fas fa-link me-1"></i>Related
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- === OVERVIEW TAB === -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-3">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Work Order Details -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-clipboard-list"></i>Work Order Details</h6>
                            <span class="badge bg-{{ $workOrder->status_badge ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status ?? 'draft')) }}
                            </span>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Status</label>
                                    <p class="fw-semibold mb-0">
                                        <span class="badge bg-{{ $workOrder->status_badge ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $workOrder->work_order_status ?? 'draft')) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Priority</label>
                                    <p class="fw-semibold mb-0">
                                        @php $priorityColors = ['low' => 'success', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger']; @endphp
                                        <span class="badge bg-{{ $priorityColors[$workOrder->priority ?? 'normal'] ?? 'secondary' }}">
                                            {{ ucfirst($workOrder->priority ?? 'normal') }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Type</label>
                                    <p class="fw-semibold mb-0">{{ ucfirst($workOrder->work_order_type ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small text-uppercase">Service Type</label>
                                    <p class="fw-semibold mb-0">
                                        @if($workOrder->service_type)
                                            <span class="badge bg-soft-primary text-primary">
                                                @php
                                                    $st = $workOrder->service_type;
                                                    $stArr = is_string($st) && str_starts_with($st, '[') ? json_decode($st, true) : (is_array($st) ? $st : [$st]);
                                                    $stArr = array_filter((array)$stArr);
                                                @endphp
                                                @if(!empty($stArr))
                                                    @foreach($stArr as $stItem)
                                                        <span class="badge bg-soft-primary text-primary me-1" style="font-weight:500;font-size:.75rem">
                                                            {{ config('service-types.list.' . $stItem . '.name', ucfirst(str_replace('_', ' ', $stItem))) }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Created</label>
                                    <p class="fw-semibold mb-0">{{ $workOrder->created_at ? $workOrder->created_at->format('M j, Y g:i A') : 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Last Updated</label>
                                    <p class="fw-semibold mb-0">{{ $workOrder->updated_at ? $workOrder->updated_at->format('M j, Y g:i A') : 'N/A' }}</p>
                                </div>
                            </div>
                            @if($workOrder->bay_number)
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase">Bay</label>
                                    <p class="fw-semibold mb-0"><span class="badge bg-secondary">Bay #{{ $workOrder->bay_number }}</span></p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Info -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-car"></i>Vehicle Information</h6>
                        </div>
                        <div class="form-section-body">
                            @if($workOrder->vehicle)
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">Vehicle</label>
                                        <p class="fw-semibold mb-0">{{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">License Plate</label>
                                        <p class="fw-semibold mb-0">{{ $workOrder->vehicle->license_plate ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">VIN</label>
                                        <p class="fw-semibold mb-0 text-monospace">{{ $workOrder->vehicle->vin ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted small text-uppercase">Color</label>
                                        <p class="fw-semibold mb-0">{{ $workOrder->vehicle->color ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-1"></i>No vehicle assigned</p>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Concerns -->
                    @if($workOrder->customer_concerns)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-question-circle text-warning"></i>Customer Concerns</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $workOrder->customer_concerns }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Diagnosis -->
                    @if($workOrder->diagnosis || $workOrder->initial_diagnosis)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-stethoscope text-info"></i>Diagnosis</h6>
                        </div>
                        <div class="form-section-body">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $workOrder->diagnosis ?? $workOrder->initial_diagnosis }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Cost Summary -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-dollar-sign"></i>Cost Summary</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Estimated Total</small>
                                    <span class="fw-bold">₱{{ number_format($workOrder->estimated_total ?? 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Actual Total</small>
                                    <span class="fw-bold">₱{{ number_format($workOrder->actual_total ?? 0, 2) }}</span>
                                </div>
                            </div>
                            @if(($workOrder->estimated_total ?? 0) > 0)
                            <div class="mt-2 pt-2 border-top">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Variance</small>
                                    @php $variance = ($workOrder->actual_total ?? 0) - ($workOrder->estimated_total ?? 0); @endphp
                                    <span class="fw-bold {{ $variance > 0 ? 'text-danger' : 'text-success' }}">
                                        @if($variance > 0)+@endif₱{{ number_format($variance, 2) }}
                                    </span>
                                </div>
                            </div>
                            @endif
                            @if($workOrder->estimated_labor_hours)
                            <div class="mt-2 pt-2 border-top">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Labor Hours</small>
                                    <span class="fw-bold">{{ $workOrder->estimated_labor_hours }} hrs</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Assigned Staff -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-users"></i>Assigned Staff</h6>
                        </div>
                        <div class="form-section-body">
                            @php
                                $assignments = is_array($workOrder->technician_assignments)
                                    ? $workOrder->technician_assignments
                                    : (json_decode($workOrder->technician_assignments ?? '[]', true) ?? []);
                            @endphp
                            @if(count($assignments) > 0)
                                @foreach($assignments as $assignment)
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user-cog text-primary me-2"></i>
                                        <div>
                                            <strong>{{ $assignment['name'] ?? $assignment['technician_name'] ?? 'Technician' }}</strong>
                                            @if(isset($assignment['role']))
                                                <br><small class="text-muted">{{ $assignment['role'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @elseif($workOrder->technician)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-cog text-primary me-2"></i>
                                    <strong>{{ $workOrder->technician->name }}</strong>
                                </div>
                            @else
                                <p class="text-muted mb-0 small">No technicians assigned</p>
                            @endif
                            @if($workOrder->advisor ?? $workOrder->serviceAdvisor)
                                <hr class="my-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-tie text-info me-2"></i>
                                    <strong>{{ ($workOrder->advisor ?? $workOrder->serviceAdvisor)->name }}</strong>
                                    <small class="text-muted ms-2">(Advisor)</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Condition -->
                    @if($workOrder->odometer_in || $workOrder->fuel_level || $workOrder->vehicle_condition)
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-clipboard-check"></i>Vehicle Condition</h6>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-2 small">
                                @if($workOrder->odometer_in)
                                <div class="col-6"><span class="text-muted">Odometer:</span></div>
                                <div class="col-6 text-end">{{ number_format($workOrder->odometer_in) }} km</div>
                                @endif
                                @if($workOrder->fuel_level)
                                <div class="col-6"><span class="text-muted">Fuel:</span></div>
                                <div class="col-6 text-end">{{ $workOrder->fuel_level }}/4</div>
                                @endif
                                @if($workOrder->vehicle_condition)
                                <div class="col-12 mt-2"><span class="text-muted">Condition:</span></div>
                                <div class="col-12"><p class="mb-0 small">{{ $workOrder->vehicle_condition }}</p></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Related Records -->
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-link"></i>Related Records</h6>
                        </div>
                        <div class="form-section-body">
                            @php
                                $relations = [];
                                if($workOrder->appointment) $relations[] = ['name' => 'Appointment', 'icon' => 'fa-calendar-check', 'route' => route('appointments.show', $workOrder->appointment), 'color' => 'info'];
                                if($workOrder->vehicleInspection) $relations[] = ['name' => 'Inspection', 'icon' => 'fa-search', 'route' => route('inspections.show', $workOrder->vehicleInspection), 'color' => 'primary'];
                                if($workOrder->estimate) $relations[] = ['name' => 'Estimate', 'icon' => 'fa-file-invoice-dollar', 'route' => route('estimates.show', $workOrder->estimate), 'color' => 'success'];
                            @endphp
                            @if(count($relations) > 0)
                                @foreach($relations as $rel)
                                    <a href="{{ $rel['route'] }}" class="btn btn-outline-{{ $rel['color'] }} btn-sm w-100 mb-2 text-start">
                                        <i class="fas {{ $rel['icon'] }} me-1"></i>{{ $rel['name'] }}
                                    </a>
                                @endforeach
                            @else
                                <p class="text-muted mb-0 small">No related records.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- === SERVICES & ITEMS TAB === -->
        <div class="tab-pane fade" id="services" role="tabpanel">
            @php $items = $workOrder->items ?? collect(); @endphp
            @if($items->count() > 0)
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6><i class="fas fa-tools"></i>Service Items ({{ $items->count() }})</h6>
                    </div>
                    <div class="form-section-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Est. Cost</th>
                                        <th class="text-end">Actual Cost</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td class="fw-medium">{{ $item->name ?? $item->item_name }}</td>
                                            <td class="small text-muted">{{ $item->description ?? '' }}</td>
                                            <td class="text-end">{{ $item->quantity ?? 1 }}</td>
                                            <td class="text-end">₱{{ number_format($item->estimated_cost ?? 0, 2) }}</td>
                                            <td class="text-end">₱{{ number_format($item->actual_cost ?? 0, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->status == 'completed' ? 'success' : ($item->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                    {{ $item->status ?? 'pending' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Totals:</td>
                                        <td class="text-end fw-bold">₱{{ number_format($items->sum('estimated_cost'), 2) }}</td>
                                        <td class="text-end fw-bold">₱{{ number_format($items->sum('actual_cost'), 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6><i class="fas fa-tools"></i>Service Items</h6>
                    </div>
                    <div class="form-section-body text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No service items added yet.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- === TECHNICIANS TAB === -->
        <div class="tab-pane fade" id="techs" role="tabpanel">
            @php
                $assignments = is_array($workOrder->technician_assignments)
                    ? $workOrder->technician_assignments
                    : (json_decode($workOrder->technician_assignments ?? '[]', true) ?? []);
            @endphp
            @if(count($assignments) > 0)
                <div class="row g-3">
                    @foreach($assignments as $assignment)
                        <div class="col-md-6 col-lg-4">
                            <div class="form-section">
                                <div class="form-section-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded-circle bg-{{ $assignment['color'] ?? 'primary' }} text-white d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;font-size:1.2rem;">
                                            <i class="fas fa-user-cog"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $assignment['name'] ?? $assignment['technician_name'] ?? 'Technician' }}</strong>
                                            <br><small class="text-muted">{{ $assignment['role'] ?? 'Technician' }}</small>
                                        </div>
                                    </div>
                                    @if(isset($assignment['hours']))
                                        <div class="mt-2">
                                            <small class="text-muted">Hours: {{ $assignment['hours'] }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($workOrder->technician)
                <div class="col-md-6">
                    <div class="form-section">
                        <div class="form-section-body">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div>
                                    <strong>{{ $workOrder->technician->name }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="form-section">
                    <div class="form-section-header no-collapse">
                        <h6><i class="fas fa-users"></i>Technician Assignments</h6>
                    </div>
                    <div class="form-section-body text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No technicians assigned to this work order.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- === TIMELINE TAB === -->
        <div class="tab-pane fade" id="timeline" role="tabpanel">
            <div class="form-section">
                <div class="form-section-header no-collapse">
                    <h6><i class="fas fa-history"></i>Work Order Timeline</h6>
                </div>
                <div class="form-section-body">
                    @php
                        $events = [];
                        if($workOrder->created_at) $events[] = ['event' => 'Created', 'time' => $workOrder->created_at, 'icon' => 'fa-plus-circle', 'color' => 'primary'];
                        if($workOrder->started_at) $events[] = ['event' => 'Work Started', 'time' => $workOrder->started_at, 'icon' => 'fa-play', 'color' => 'info'];
                        if($workOrder->completed_at) $events[] = ['event' => 'Completed', 'time' => $workOrder->completed_at, 'icon' => 'fa-check-circle', 'color' => 'success'];
                        if(method_exists($workOrder, 'checkedInAt') && $workOrder->checked_in_at) $events[] = ['event' => 'Checked In', 'time' => $workOrder->checked_in_at, 'icon' => 'fa-sign-in-alt', 'color' => 'primary'];
                        if(method_exists($workOrder, 'pickedUpAt') && $workOrder->picked_up_at) $events[] = ['event' => 'Picked Up', 'time' => $workOrder->picked_up_at, 'icon' => 'fa-car', 'color' => 'success'];
                    @endphp
                    @if(count($events) > 0)
                        <div class="timeline-vertical">
                            @foreach(collect($events)->sortBy('time') as $entry)
                                <div class="d-flex mb-3">
                                    <div class="me-3 text-{{ $entry['color'] }}" style="font-size: 1.2rem;">
                                        <i class="fas {{ $entry['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $entry['event'] }}</strong>
                                        <br><small class="text-muted">{{ $entry['time'] instanceof \Carbon\Carbon ? $entry['time']->format('M j, Y g:i A') : \Carbon\Carbon::parse($entry['time'])->format('M j, Y g:i A') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No timeline events available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- === RELATED TAB === -->
        <div class="tab-pane fade" id="related" role="tabpanel">
            <div class="row g-3">
                @if($workOrder->appointment)
                <div class="col-md-6 col-lg-4">
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-calendar-check"></i>Appointment</h6>
                        </div>
                        <div class="form-section-body">
                            <strong>#{{ $workOrder->appointment->id }}</strong>
                            <span class="badge bg-{{ $workOrder->appointment->status_badge ?? 'secondary' }} ms-2">
                                {{ $workOrder->appointment->appointment_status ?? '' }}
                            </span>
                            <div class="mt-2">
                                <a href="{{ route('appointments.show', $workOrder->appointment) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($workOrder->vehicleInspection)
                <div class="col-md-6 col-lg-4">
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-search"></i>Inspection</h6>
                        </div>
                        <div class="form-section-body">
                            <strong>{{ $workOrder->vehicleInspection->inspection_name ?? 'Inspection #'.$workOrder->vehicleInspection->id }}</strong>
                            <span class="badge bg-{{ $workOrder->vehicleInspection->status_badge ?? 'secondary' }} ms-2">
                                {{ ucfirst(str_replace('_', ' ', $workOrder->vehicleInspection->inspection_status ?? '')) }}
                            </span>
                            <div class="mt-2">
                                <a href="{{ route('inspections.show', $workOrder->vehicleInspection) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($workOrder->estimate)
                <div class="col-md-6 col-lg-4">
                    <div class="form-section">
                        <div class="form-section-header no-collapse">
                            <h6><i class="fas fa-file-invoice-dollar"></i>Estimate</h6>
                        </div>
                        <div class="form-section-body">
                            <strong>{{ $workOrder->estimate->estimate_number ?? 'Estimate #'.$workOrder->estimate->id }}</strong>
                            <span class="badge bg-{{ $workOrder->estimate->status_badge ?? 'secondary' }} ms-2">
                                {{ ucfirst($workOrder->estimate->status ?? 'draft') }}
                            </span>
                            <div class="mt-2">
                                <a href="{{ route('estimates.show', $workOrder->estimate) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($workOrder->vehicleInspection && $workOrder->vehicleInspection->inspectionFindings()->count() > 0)
    <!-- Inspection Findings Section -->
    <div class="form-section mt-4">
        <div class="form-section-header no-collapse">
            <h6><i class="fas fa-clipboard-list"></i>Inspection Findings</h6>
            <span class="badge bg-primary ms-2">{{ $workOrder->vehicleInspection->inspectionFindings()->count() }} findings</span>
        </div>
        <div class="form-section-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Issue</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Urgency</th>
                            <th>Est. Cost</th>
                            <th>Technician</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workOrder->vehicleInspection->inspectionFindings as $fIdx => $finding)
                        <tr>
                            <td class="text-muted">{{ $fIdx + 1 }}</td>
                            <td>
                                <strong>{{ $finding->issue_title }}</strong>
                                @if($finding->detailed_notes)
                                <br><small class="text-muted">{{ Str::limit($finding->detailed_notes, 80) }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $finding->category }}</span></td>
                            <td>
                                @switch($finding->severity)
                                    @case('low') <span class="badge bg-info">Low</span> @break
                                    @case('medium') <span class="badge bg-warning text-dark">Medium</span> @break
                                    @case('high') <span class="badge bg-danger">High</span> @break
                                    @case('critical') <span class="badge bg-dark">Critical</span> @break
                                @endswitch
                            </td>
                            <td>
                                @switch($finding->estimated_urgency)
                                    @case('routine') <span class="badge bg-secondary">Routine</span> @break
                                    @case('soon') <span class="badge bg-info">Soon</span> @break
                                    @case('urgent') <span class="badge bg-warning text-dark">Urgent</span> @break
                                    @case('immediate') <span class="badge bg-danger">Immediate</span> @break
                                @endswitch
                            </td>
                            <td class="fw-semibold">
                                @if($finding->estimated_cost !== null)
                                ₱{{ number_format($finding->estimated_cost, 2) }}
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $finding->technician ? $finding->technician->name : ($inspection->technician->name ?? '—') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
