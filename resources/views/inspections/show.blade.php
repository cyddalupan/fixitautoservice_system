@extends('layouts.app')

@section('title', 'Vehicle Inspection #' . $inspection->inspection_number . ' - Fix-It Auto Services')

@section('content')
<!-- Main Form for Editing Inspection -->
<form id="inspection-edit-form" action="{{ route('inspections.update', $inspection) }}" method="POST">
    @csrf
    @method('PUT')
    
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>Vehicle Inspection #{{ $inspection->inspection_number }}
            </h1>
            <p class="text-muted mb-0">
                {{ $inspection->customer->full_name ?? 'Unknown Customer' }} | 
                {{ $inspection->vehicle->make ?? '' }} {{ $inspection->vehicle->model ?? '' }} | 
                {{ $inspection->vehicle->year ?? '' }} | 
                {{ $inspection->vehicle->license_plate ?? 'No Plate' }}
            </p>
        </div>
        <div>
            <a href="{{ route('inspections.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Inspections
            </a>
            
            <!-- Save Draft Button -->
            <div class="btn-group ms-2" role="group">
                <!-- Save Draft Button -->
                @if($inspection->inspection_status === 'in_progress')
                    <button type="submit" name="status" value="draft" class="btn btn-primary" onclick="return confirm('Save inspection as draft? You can continue later.')">
                        <i class="fas fa-save me-1"></i> Save Draft
                    </button>
                @else
                    <button type="button" class="btn btn-primary disabled">
                        <i class="fas fa-save me-1"></i> Save Draft
                    </button>
                @endif
            </div>
            
            <!-- Quick Actions Buttons -->
            <div class="btn-group ms-2" role="group">
                <!-- Create Estimate Button -->
                @if($inspection->inspection_status === 'completed' && !$inspection->workOrder)
                    <a href="{{ route('estimates.create', ['inspection_id' => $inspection->id]) }}" class="btn btn-success">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                    </a>
                @else
                    <button type="button" class="btn btn-success disabled">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Create Estimate
                    </button>
                @endif
                
                <!-- Report Button -->
                <a href="{{ route('inspections.report', $inspection) }}" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-print me-1"></i> Report
                </a>
                
                <!-- View Work Order Button -->
                @if($inspection->workOrder)
                    <a href="{{ route('work-orders.show', $inspection->workOrder->id) }}" class="btn btn-info">
                        <i class="fas fa-wrench me-1"></i> Work Order
                    </a>
                @endif
                
                <!-- View Appointment Button -->
                @if($inspection->appointment)
                    <a href="{{ route('appointments.show', $inspection->appointment) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar me-1"></i> Appointment
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Status Badge -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center">
            <span class="badge 
                @if($inspection->inspection_status === 'completed') bg-success
                @elseif($inspection->inspection_status === 'in_progress') bg-warning
                @elseif($inspection->inspection_status === 'draft') bg-secondary
                @elseif($inspection->inspection_status === 'cancelled') bg-danger
                @else bg-info @endif
                fs-6 px-3 py-2 me-3">
                {{ ucfirst(str_replace('_', ' ', $inspection->status)) }}
            </span>
            
            <div class="d-inline-block me-3">
                <select class="form-select form-select-sm" name="inspection_type" style="width: auto; display: inline-block;">
                    <option value="">Select Inspection Type</option>
                    <option value="pre_purchase" {{ old('inspection_type', $inspection->inspection_type) == 'pre_purchase' ? 'selected' : '' }}>Pre-Purchase Inspection</option>
                    <option value="routine_maintenance" {{ old('inspection_type', $inspection->inspection_type) == 'routine_maintenance' ? 'selected' : '' }}>Routine Maintenance</option>
                    <option value="diagnostic" {{ old('inspection_type', $inspection->inspection_type) == 'diagnostic' ? 'selected' : '' }}>Diagnostic Inspection</option>
                    <option value="safety" {{ old('inspection_type', $inspection->inspection_type) == 'safety' ? 'selected' : '' }}>Safety Inspection</option>
                    <option value="emissions" {{ old('inspection_type', $inspection->inspection_type) == 'emissions' ? 'selected' : '' }}>Emissions Inspection</option>
                    <option value="custom" {{ old('inspection_type', $inspection->inspection_type) == 'custom' ? 'selected' : '' }}>Custom Inspection</option>
                </select>
            </div>
            
            @if($inspection->inspection_score)
                <span class="badge 
                    @if($inspection->inspection_score >= 90) bg-success
                    @elseif($inspection->inspection_score >= 70) bg-warning
                    @else bg-danger @endif
                    fs-6 px-3 py-2">
                    Score: {{ $inspection->inspection_score }}%
                </span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column - Inspection Details -->
    <div class="col-md-8">
        <!-- Customer & Vehicle Information -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Customer & Vehicle Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Customer Details</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $inspection->customer->full_name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $inspection->customer->phone ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $inspection->customer->email ?? 'N/A' }}</p>
                        
                        @if($inspection->appointment)
                            <div class="mt-3">
                                <h6 class="text-muted mb-2">Appointment</h6>
                                <p class="mb-1"><strong>Appointment #:</strong> {{ $inspection->appointment->appointment_number ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Service Request:</strong> {{ $inspection->appointment->service_request ?? 'N/A' }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Vehicle Details</h6>
                        <p class="mb-1"><strong>Make:</strong> {{ $inspection->vehicle->make ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Model:</strong> {{ $inspection->vehicle->model ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Year:</strong> {{ $inspection->vehicle->year ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>License Plate:</strong> {{ $inspection->vehicle->license_plate ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>VIN:</strong> {{ $inspection->vehicle->vin ?? 'N/A' }}</p>
                        <p class="mb-1">
                            <strong>Mileage:</strong> 
                            <input type="number" class="form-control form-control-sm d-inline-block w-auto ms-1" 
                                   name="vehicle_mileage" value="{{ old('vehicle_mileage', $inspection->vehicle_mileage) }}" 
                                   min="0" step="1" style="width: 120px;"> km
                        </p>
                    </div>
                </div>
                
                <div class="mt-3" id="customer-concerns-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Customer Concerns</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="edit-concerns-btn" onclick="editCustomerConcerns()">
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>
                    </div>
                    
                    <!-- Single container that switches between view and edit -->
                    <div id="concerns-container" class="border rounded p-3 bg-light">
                        <!-- View Mode Content -->
                        <div id="concerns-view">
                            <p class="mb-0" id="concerns-text">{{ $inspection->customer_concerns ?? 'No concerns recorded' }}</p>
                        </div>
                        
                        <!-- Edit Mode Content (Hidden by default) -->
                        <div id="concerns-edit" style="display: none;">
                            <textarea class="form-control mb-2" id="concerns-input" rows="3" placeholder="Enter customer concerns here...">{{ old('customer_concerns', $inspection->customer_concerns) }}</textarea>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-success me-1" onclick="saveCustomerConcerns()">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEditConcerns()">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3" id="technician-notes-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Technician Notes</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="edit-notes-btn" onclick="editTechnicianNotes()">
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>
                    </div>
                    
                    <!-- Single container that switches between view and edit -->
                    <div id="notes-container" class="border rounded p-3 bg-info bg-opacity-10">
                        <!-- View Mode Content -->
                        <div id="notes-view">
                            <p class="mb-0" id="notes-text">{{ $inspection->technician_notes ?? 'No notes recorded' }}</p>
                        </div>
                        
                        <!-- Edit Mode Content (Hidden by default) -->
                        <div id="notes-edit" style="display: none;">
                            <textarea class="form-control mb-2" id="notes-input" rows="3" placeholder="Enter technician notes here...">{{ old('technician_notes', $inspection->technician_notes) }}</textarea>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-success me-1" onclick="saveTechnicianNotes()">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEditNotes()">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inspection Items by Category -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>Inspection Items
                </h5>
                <div>
                    <span class="badge bg-success me-2">Passed: {{ $itemStats['passed'] ?? 0 }}</span>
                    <span class="badge bg-danger me-2">Failed: {{ $itemStats['failed'] ?? 0 }}</span>
                    <span class="badge bg-warning">Attention: {{ $itemStats['attention_needed'] ?? 0 }}</span>
                </div>
            </div>
            
            <!-- Inspection Action Buttons - Moved here as requested -->
            <div class="card-body border-bottom bg-light">
                <div class="row g-2">
                    <!-- Add Findings Button -->
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100 
                            @if(in_array($inspection->status, ['completed', 'cancelled'])) disabled @endif"
                            onclick="showAddFindingForm()"
                            @if(in_array($inspection->status, ['completed', 'cancelled'])) disabled @endif>
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="fs-5 me-2">➕</span>
                                <div class="text-start">
                                    <div class="fw-bold">Add Findings</div>
                                    <small class="opacity-75">Record findings</small>
                                </div>
                            </div>
                        </button>
                    </div>
                    
                    <!-- Upload Photos Button -->
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100 
                            @if(in_array($inspection->status, ['completed', 'cancelled'])) disabled @endif"
                            data-bs-toggle="modal" data-bs-target="#uploadPhotoModal"
                            @if(in_array($inspection->status, ['completed', 'cancelled'])) disabled @endif>
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="fs-5 me-2">📷</span>
                                <div class="text-start">
                                    <div class="fw-bold">Upload Photos</div>
                                    <small class="opacity-75">Add photos</small>
                                </div>
                            </div>
                        </button>
                    </div>
                    
                    
                    <!-- Complete Inspection Toggle Button -->
                    <div class="col-md-3">
                        @if($inspection->inspection_status !== 'completed')
                            <!-- Light Red Button for NOT completed -->
                            <form action="{{ route('inspections.complete', $inspection) }}" method="POST" class="w-100" autocomplete="off" id="complete-form-{{ $inspection->id }}">
                                @csrf
                                <input type="hidden" name="_cache_bust" value="{{ time() }}">
                                <button type="button" class="btn btn-danger btn-light w-100" style="background-color: #ffcccc; border-color: #ff9999; color: #cc0000;" onclick="confirmCompleteInspection({{ $inspection->id }})">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="fs-5 me-2">⭕</span>
                                        <div class="text-start">
                                            <div class="fw-bold">Mark as Complete</div>
                                            <small class="opacity-75">Click to complete inspection</small>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        @else
                            <!-- Green Button for completed - Now clickable to undo -->
                            <form action="{{ route('inspections.undo-complete', $inspection) }}" method="POST" class="w-100" autocomplete="off" id="undo-complete-form-{{ $inspection->id }}">
                                @csrf
                                <input type="hidden" name="_cache_bust" value="{{ time() }}">
                                <button type="button" class="btn btn-success w-100" style="background-color: #ccffcc; border-color: #99cc99; color: #006600;" onclick="confirmUndoCompleteInspection({{ $inspection->id }})">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="fs-5 me-2">✅</span>
                                        <div class="text-start">
                                            <div class="fw-bold">Completed</div>
                                            <small class="opacity-75">Click to undo completion</small>
                                        </div>
                                    </div>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                
                <!-- Status Note -->
                <div class="mt-2 text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        All inspection-related actions are now grouped here in the Inspection Items section
                    </small>
                </div>
            </div>
            
            <div class="card-body">
                @if($itemsByCategory && count($itemsByCategory) > 0)
                    @foreach($itemsByCategory as $categoryName => $items)
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">{{ $categoryName }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th width="60%">Item</th>
                                            <th width="20%">Status</th>
                                            <th width="20%">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td>{{ $item->item_name }}</td>
                                                <td>
                                                    @php
                                                        $statusBadge = [
                                                            'passed' => 'success',
                                                            'failed' => 'danger',
                                                            'attention_needed' => 'warning',
                                                            'not_applicable' => 'secondary'
                                                        ][$item->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $statusBadge }}">
                                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($item->notes)
                                                        <small class="text-muted">{{ $item->notes }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>No inspection items recorded yet.
                        
                        <!-- Add New Finding Form for Empty State -->
                        <div class="mt-3" id="addFindingFormEmpty" style="display: none;">
                            <div class="demo-form">
                                <div class="row g-3 justify-content-center">
                                    <div class="col-md-8">
                                        <label for="item_name_empty" class="form-label">Item Name *</label>
                                        <input type="text" class="form-control" id="item_name_empty" placeholder="What did you inspect?">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="status_empty" class="form-label">Status *</label>
                                        <select class="form-select" id="status_empty">
                                            <option value="">Select Status</option>
                                            <option value="passed">Passed</option>
                                            <option value="failed">Failed</option>
                                            <option value="attention_needed">Attention Needed</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('addFindingFormEmpty').style.display='none'">
                                                Cancel
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="addDemoFinding()">
                                                <i class="fas fa-plus me-1"></i> Add First Finding
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Add New Finding Form (Initially Hidden) - For when items exist -->
                <div class="mt-4 p-3 border rounded" id="addFindingForm" style="display: none;">
                    <h6 class="mb-3">Add New Finding</h6>
                    <div class="demo-form">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="item_name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="item_name" placeholder="e.g., Brake Pads, Oil Filter, Tire Tread">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status">
                                    <option value="">Select Status</option>
                                    <option value="passed">Passed</option>
                                    <option value="failed">Failed</option>
                                    <option value="attention_needed">Attention Needed</option>
                                    <option value="not_applicable">Not Applicable</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" placeholder="e.g., Brakes, Engine, Electrical">
                            </div>
                            
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" rows="2" placeholder="Additional notes about this finding..."></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('addFindingForm').style.display='none'">
                                        Cancel
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="addDemoFinding()">
                                        <i class="fas fa-plus me-1"></i> Add Finding
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Add Finding Button (Bottom of Card) -->
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" onclick="showAddFindingForm()">
                        <i class="fas fa-plus me-1"></i> Add New Finding
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Stats & Actions -->
    <div class="col-md-4">
        <!-- Inspection Statistics -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Inspection Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 border rounded">
                            <h3 class="mb-0 text-primary">{{ $itemStats['total'] ?? 0 }}</h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 border rounded">
                            <h3 class="mb-0 text-success">{{ $itemStats['passed'] ?? 0 }}</h3>
                            <small class="text-muted">Passed</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 border rounded">
                            <h3 class="mb-0 text-danger">{{ $itemStats['failed'] ?? 0 }}</h3>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 border rounded">
                            <h3 class="mb-0 text-warning">{{ $itemStats['attention_needed'] ?? 0 }}</h3>
                            <small class="text-muted">Needs Attention</small>
                        </div>
                    </div>
                </div>
                
                @if($itemStats['pass_rate'] !== null)
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Pass Rate</h6>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar 
                                @if($itemStats['pass_rate'] >= 90) bg-success
                                @elseif($itemStats['pass_rate'] >= 70) bg-warning
                                @else bg-danger @endif"
                                role="progressbar" 
                                style="width: {{ $itemStats['pass_rate'] }}%"
                                aria-valuenow="{{ $itemStats['pass_rate'] }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                {{ number_format($itemStats['pass_rate'], 1) }}%
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Inspection Team -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Inspection Team
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Technician</h6>
                    <p class="mb-1">
                        @if($inspection->technician)
                            <i class="fas fa-user-check me-2 text-success"></i>
                            {{ $inspection->technician->full_name }}
                        @else
                            <span class="text-muted">Not assigned</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Service Advisor</h6>
                    <p class="mb-1">
                        @if($inspection->serviceAdvisor)
                            <i class="fas fa-user-tie me-2 text-primary"></i>
                            {{ $inspection->serviceAdvisor->full_name }}
                        @else
                            <span class="text-muted">Not assigned</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Created By</h6>
                    <p class="mb-1">
                        @if($inspection->createdBy)
                            <i class="fas fa-user-plus me-2 text-info"></i>
                            {{ $inspection->createdBy->full_name }}
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </p>
                </div>
                
                @if($inspection->approvedBy)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Approved By</h6>
                        <p class="mb-1">
                            <i class="fas fa-user-check me-2 text-success"></i>
                            {{ $inspection->approvedBy->full_name }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Inspection Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($inspection->created_at)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Inspection Created</h6>
                                <small class="text-muted">{{ $inspection->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                    
                    @if($inspection->inspection_start_time)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Inspection Started</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($inspection->inspection_start_time)->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                    
                    @if($inspection->inspection_end_time)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Inspection Completed</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($inspection->inspection_end_time)->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                    
                    @if($inspection->approved_at)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Inspection Approved</h6>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($inspection->approved_at)->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Previous Inspections for This Vehicle
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Inspection #</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Technician</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicleInspections as $prevInspection)
                                <tr>
                                    <td>{{ $prevInspection->inspection_number }}</td>
                                    <td>{{ $prevInspection->created_at->format('M d, Y') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $prevInspection->inspection_type)) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($prevInspection->inspection_status === 'completed') bg-success
                                            @elseif($prevInspection->inspection_status === 'in_progress') bg-warning
                                            @elseif($prevInspection->inspection_status === 'draft') bg-secondary
                                            @elseif($prevInspection->inspection_status === 'cancelled') bg-danger
                                            @else bg-info @endif">
                                            {{ ucfirst(str_replace('_', ' ', $prevInspection->inspection_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($prevInspection->inspection_score)
                                            <span class="badge 
                                                @if($prevInspection->inspection_score >= 90) bg-success
                                                @elseif($prevInspection->inspection_score >= 70) bg-warning
                                                @else bg-danger @endif">
                                                {{ $prevInspection->inspection_score }}%
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $prevInspection->technician->full_name ?? 'Not assigned' }}</td>
                                    <td>
                                        <a href="{{ route('inspections.show', $prevInspection) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Photos Section - At the very bottom of inspection items -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-images me-2"></i>Inspection Photos
                </h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal"
                    @if(in_array($inspection->status, ['completed', 'cancelled'])) disabled @endif>
                    <i class="fas fa-plus me-1"></i> Add Photo
                </button>
            </div>
            <div class="card-body" id="photos-container">
                @php
                    $photos = $inspection->photos ?? [];
                @endphp
                
                @if(count($photos) > 0)
                    <div class="row">
                        @foreach($photos as $index => $photo)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <a href="{{ asset('storage/' . $photo['path']) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $photo['path']) }}" 
                                             class="card-img-top" 
                                             alt="Inspection photo {{ $index + 1 }}"
                                             style="height: 150px; object-fit: cover;">
                                    </a>
                                    <div class="card-body p-2">
                                        @if(!empty($photo['description']))
                                            <p class="card-text small mb-1">{{ $photo['description'] }}</p>
                                        @endif
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ \Carbon\Carbon::parse($photo['uploaded_at'])->format('M d, Y h:i A') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-camera fa-3x text-muted"></i>
                        </div>
                        <h6 class="text-muted">No photos uploaded yet</h6>
                        <p class="text-muted small">Click "Add Photo" to upload inspection photos</p>
                        <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal"
                            @if(in_array($inspection->status, ['completed', 'cancelled'])) disabled @endif>
                            <i class="fas fa-upload me-2"></i>Upload First Photo
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

</form>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -20px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endpush

@push('scripts')
<script>
// Cache Busting: Force fresh page load if page is cached
(function() {
    // Check if this is a cached page by looking for a timestamp
    if (!sessionStorage.getItem('pageLoaded_' + window.location.pathname)) {
        // First load of this page in this session
        sessionStorage.setItem('pageLoaded_' + window.location.pathname, Date.now());
    } else {
        // Page might be cached, force a hard refresh
        const lastLoad = parseInt(sessionStorage.getItem('pageLoaded_' + window.location.pathname));
        const currentTime = Date.now();
        const timeDiff = currentTime - lastLoad;
        
        // If page was loaded more than 5 minutes ago, it might be stale
        if (timeDiff > 5 * 60 * 1000) {
            console.log('Page might be stale, forcing hard refresh...');
            sessionStorage.setItem('pageLoaded_' + window.location.pathname, currentTime);
            window.location.reload(true); // Force hard refresh
        }
    }
    
    // Add timestamp to all form submissions to prevent caching
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            // Check if form already has cache busting field
            if (!form.querySelector('input[name="_cache_bust"]')) {
                const cacheBustField = document.createElement('input');
                cacheBustField.type = 'hidden';
                cacheBustField.name = '_cache_bust';
                cacheBustField.value = Date.now();
                form.appendChild(cacheBustField);
            }
            
            // Add form submission logging for debugging
            form.addEventListener('submit', function(e) {
                console.log('Form submitting:', form.action);
                console.log('Form method:', form.method);
                console.log('Form data:', new FormData(form));
            });
        });
        
        // Log all button clicks for debugging
        document.addEventListener('click', function(e) {
            if (e.target.type === 'submit' || e.target.closest('button[type="submit"]')) {
                console.log('Submit button clicked:', e.target);
            }
        });
    });
})();

function showAddFindingForm() {
    // Check if there are existing items
    const hasItems = {{ $itemsByCategory && count($itemsByCategory) > 0 ? 'true' : 'false' }};
    
    if (hasItems) {
        // Show form at bottom of items list
        document.getElementById('addFindingForm').style.display = 'block';
        // Scroll to form
        document.getElementById('addFindingForm').scrollIntoView({ behavior: 'smooth' });
        // Focus on first input
        document.getElementById('item_name').focus();
    } else {
        // Show form for empty state
        document.getElementById('addFindingFormEmpty').style.display = 'block';
        // Scroll to form
        document.getElementById('addFindingFormEmpty').scrollIntoView({ behavior: 'smooth' });
        // Focus on first input
        document.getElementById('item_name').focus();
    }
}

// Function to add finding directly (creates input row in table)
function addDemoFinding() {
    // Get values from form
    const itemName = document.getElementById('item_name') ? document.getElementById('item_name').value : document.getElementById('item_name_empty').value;
    const status = document.getElementById('status') ? document.getElementById('status').value : document.getElementById('status_empty').value;
    const category = document.getElementById('category') ? document.getElementById('category').value : 'Uncategorized';
    const notes = document.getElementById('notes') ? document.getElementById('notes').value : '';
    
    if (!itemName || !status) {
        alert('Please fill in Item Name and Status');
        return;
    }
    
    // Create a demo finding (client-side only)
    alert('Demo: Finding "' + itemName + '" added with status: ' + status + '\n\nIn a real implementation, this would save to the database.\n\nItem: ' + itemName + '\nStatus: ' + status + '\nCategory: ' + category + '\nNotes: ' + notes);
    
    // Hide the form
    document.getElementById('addFindingForm').style.display = 'none';
    if (document.getElementById('addFindingFormEmpty')) {
        document.getElementById('addFindingFormEmpty').style.display = 'none';
    }
    
    // Clear form fields
    if (document.getElementById('item_name')) document.getElementById('item_name').value = '';
    if (document.getElementById('item_name_empty')) document.getElementById('item_name_empty').value = '';
    if (document.getElementById('status')) document.getElementById('status').value = '';
    if (document.getElementById('status_empty')) document.getElementById('status_empty').value = '';
    if (document.getElementById('category')) document.getElementById('category').value = '';
    if (document.getElementById('notes')) document.getElementById('notes').value = '';
}

function addFindingInput() {
    const tableBody = document.querySelector('.inspection-items-table tbody');
    if (!tableBody) return;
    
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <input type="text" class="form-control form-control-sm" name="new_items[0][item_name]" placeholder="Item name" required>
        </td>
        <td>
            <select class="form-select form-select-sm" name="new_items[0][status]" required>
                <option value="">Select</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="attention_needed">Attention Needed</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" name="new_items[0][notes]" placeholder="Notes">
            <input type="hidden" name="new_items[0][inspection_id]" value="{{ $inspection->id }}">
        </td>
    `;
    
    tableBody.appendChild(newRow);
    newRow.querySelector('input').focus();
}

// Initialize page state on load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inspection page loaded');
    
    // Ensure view mode is visible for both sections
    document.getElementById('concerns-view').style.display = 'block';
    document.getElementById('notes-view').style.display = 'block';
    
    // Ensure edit mode is hidden
    document.getElementById('concerns-edit').style.display = 'none';
    document.getElementById('notes-edit').style.display = 'none';
    
    // Ensure edit buttons are visible
    document.getElementById('edit-concerns-btn').style.display = 'block';
    document.getElementById('edit-notes-btn').style.display = 'block';
    
    // Add event listeners to prevent automatic hiding
    const concernsInput = document.getElementById('concerns-input');
    const notesInput = document.getElementById('notes-input');
    
    if (concernsInput) {
        concernsInput.addEventListener('blur', function(e) {
            console.log('concerns-input blur event at', new Date().toISOString());
            // Don't do anything on blur - let user decide when to save/cancel
        });
    }
    
    if (notesInput) {
        notesInput.addEventListener('blur', function(e) {
            console.log('notes-input blur event at', new Date().toISOString());
            // Don't do anything on blur - let user decide when to save/cancel
        });
    }
    
    // Monitor for any attempts to hide our edit divs
    const concernsEdit = document.getElementById('concerns-edit');
    const notesEdit = document.getElementById('notes-edit');
    
    if (concernsEdit) {
        const originalSetAttribute = concernsEdit.setAttribute;
        concernsEdit.setAttribute = function(name, value) {
            if (name === 'style' && value.includes('display: none')) {
                console.log('Blocked attempt to hide concerns-edit via setAttribute');
                return; // Block the hiding
            }
            return originalSetAttribute.apply(this, arguments);
        };
    }
    
    if (notesEdit) {
        const originalSetAttribute = notesEdit.setAttribute;
        notesEdit.setAttribute = function(name, value) {
            if (name === 'style' && value.includes('display: none')) {
                console.log('Blocked attempt to hide notes-edit via setAttribute');
                return; // Block the hiding
            }
            return originalSetAttribute.apply(this, arguments);
        };
    }
});

// Customer Concerns Functions
function editCustomerConcerns() {
    console.log('editCustomerConcerns called');
    
    // Hide view mode, show edit mode
    document.getElementById('concerns-view').style.display = 'none';
    document.getElementById('concerns-edit').style.display = 'block';
    document.getElementById('edit-concerns-btn').style.display = 'none';
    
    // Focus on textarea
    document.getElementById('concerns-input').focus();
}

function cancelEditConcerns() {
    // Get original value
    const originalText = document.getElementById('concerns-text').textContent;
    
    // Reset input to original value
    document.getElementById('concerns-input').value = originalText === 'No concerns recorded' ? '' : originalText;
    
    // Always show view mode, hide edit mode
    document.getElementById('concerns-view').style.display = 'block';
    document.getElementById('concerns-edit').style.display = 'none';
    document.getElementById('edit-concerns-btn').style.display = 'block';
}

function saveCustomerConcerns() {
    const concerns = document.getElementById('concerns-input').value.trim();
    const saveBtn = document.querySelector('#concerns-edit .btn-success');
    const originalText = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    saveBtn.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('customer_concerns', concerns);
    formData.append('_method', 'PUT');
    
    fetch("{{ route('inspections.update', $inspection) }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update view text
            const displayText = concerns || 'No concerns recorded';
            document.getElementById('concerns-text').textContent = displayText;
            
            // Show view mode, hide edit mode
            document.getElementById('concerns-view').style.display = 'block';
            document.getElementById('concerns-edit').style.display = 'none';
            document.getElementById('edit-concerns-btn').style.display = 'block';
            
            // Show success message
            showToast('Customer concerns saved successfully!', 'success');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save customer concerns. Please try again.');
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Technician Notes Functions
function editTechnicianNotes() {
    console.log('editTechnicianNotes called');
    
    // Hide view mode, show edit mode
    document.getElementById('notes-view').style.display = 'none';
    document.getElementById('notes-edit').style.display = 'block';
    document.getElementById('edit-notes-btn').style.display = 'none';
    
    // Focus on textarea
    document.getElementById('notes-input').focus();
}

function cancelEditNotes() {
    // Get original value
    const originalText = document.getElementById('notes-text').textContent;
    
    // Reset input to original value
    document.getElementById('notes-input').value = originalText === 'No notes recorded' ? '' : originalText;
    
    // Always show view mode, hide edit mode
    document.getElementById('notes-view').style.display = 'block';
    document.getElementById('notes-edit').style.display = 'none';
    document.getElementById('edit-notes-btn').style.display = 'block';
}

function saveTechnicianNotes() {
    const notes = document.getElementById('notes-input').value.trim();
    const saveBtn = document.querySelector('#notes-edit .btn-success');
    const originalText = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    saveBtn.disabled = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('technician_notes', notes);
    formData.append('_method', 'PUT');
    
    fetch("{{ route('inspections.update', $inspection) }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update view text
            const displayText = notes || 'No notes recorded';
            document.getElementById('notes-text').textContent = displayText;
            
            // Show view mode, hide edit mode
            document.getElementById('notes-view').style.display = 'block';
            document.getElementById('notes-edit').style.display = 'none';
            document.getElementById('edit-notes-btn').style.display = 'block';
            
            // Show success message
            showToast('Technician notes saved successfully!', 'success');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save technician notes. Please try again.');
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Toast notification function
function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1050';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// Photo Upload Functions
function uploadPhoto() {
    const form = document.getElementById('photoUploadForm');
    const formData = new FormData(form);
    const uploadBtn = document.getElementById('uploadPhotoBtn');
    const originalText = uploadBtn.innerHTML;
    
    // Show loading state
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
    uploadBtn.disabled = true;
    
    fetch("{{ route('inspections.upload-photo', $inspection) }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Photo uploaded successfully!', 'success');
            
            // Reset form
            form.reset();
            document.getElementById('photoPreviewContainer').style.display = 'none';
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadPhotoModal'));
            modal.hide();
            
            // Dynamically add the new photo to the photos section
            addPhotoToGallery(data.photo);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to upload photo. Please try again.');
    })
    .finally(() => {
        // Restore button state
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
    });
}

// Preview photo before upload
function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    const previewContainer = document.getElementById('photoPreviewContainer');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
}

// Add photo to gallery dynamically
function addPhotoToGallery(photoData) {
    const photosContainer = document.getElementById('photos-container');
    const emptyState = photosContainer.querySelector('.text-center');
    const existingRow = photosContainer.querySelector('.row');
    
    // If empty state exists, hide it
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    // Create photo card HTML
    const photoCard = `
        <div class="col-md-4 mb-3">
            <div class="card">
                <a href="${photoData.path_url}" target="_blank">
                    <img src="${photoData.path_url}" 
                         class="card-img-top" 
                         alt="Inspection photo"
                         style="height: 150px; object-fit: cover;">
                </a>
                <div class="card-body p-2">
                    ${photoData.description ? `<p class="card-text small mb-1">${photoData.description}</p>` : ''}
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        ${new Date(photoData.uploaded_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })}
                    </small>
                </div>
            </div>
        </div>
    `;
    
    // Add photo to container
    if (existingRow) {
        existingRow.insertAdjacentHTML('beforeend', photoCard);
    } else {
        // Create row if it doesn't exist
        photosContainer.innerHTML = `
            <div class="row">
                ${photoCard}
            </div>
        `;
    }
}

// SweetAlert2 Confirmation for Completing Inspection
function confirmCompleteInspection(inspectionId) {
    console.log('confirmCompleteInspection called for inspection:', inspectionId);
    console.log('SweetAlert2 available:', typeof Swal !== 'undefined');
    
    // Check if SweetAlert2 is available
    if (typeof Swal === 'undefined') {
        console.log('Using native confirm fallback');
        // Fallback to native confirm
        if (confirm('Mark this inspection as completed?')) {
            console.log('Native confirm accepted, submitting form');
            document.getElementById('complete-form-' + inspectionId).submit();
        } else {
            console.log('Native confirm cancelled');
        }
        return;
    }
    
    Swal.fire({
        title: 'Mark as Complete?',
        text: 'Are you sure you want to mark this inspection as completed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark as complete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form
            document.getElementById('complete-form-' + inspectionId).submit();
        }
    });
}

// SweetAlert2 Confirmation for Undoing Completion
function confirmUndoCompleteInspection(inspectionId) {
    console.log('confirmUndoCompleteInspection called for inspection:', inspectionId);
    console.log('SweetAlert2 available:', typeof Swal !== 'undefined');
    
    // Check if SweetAlert2 is available
    if (typeof Swal === 'undefined') {
        console.log('Using native confirm fallback');
        // Fallback to native confirm
        if (confirm('Are you sure you want to mark this inspection as incomplete?\n\nThis will notify the technician that their inspection has been undone.')) {
            console.log('Native confirm accepted, submitting form');
            document.getElementById('undo-complete-form-' + inspectionId).submit();
        } else {
            console.log('Native confirm cancelled');
        }
        return;
    }
    
    Swal.fire({
        title: 'Undo Completion?',
        html: 'Are you sure you want to mark this inspection as incomplete?<br><br><small>This will notify the technician that their inspection has been undone.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, undo completion!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form
            document.getElementById('undo-complete-form-' + inspectionId).submit();
        }
    });
}
</script>
@endpush

<!-- Photo Upload Modal -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadPhotoModalLabel">
                    <i class="fas fa-camera me-2"></i>Upload Inspection Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="photoUploadForm" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Photo Preview -->
                    <div id="photoPreviewContainer" class="mb-3 text-center" style="display: none;">
                        <img id="photoPreview" src="#" alt="Photo preview" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    
                    <!-- Photo Upload -->
                    <div class="mb-3">
                        <label for="photo" class="form-label">Select Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required onchange="previewPhoto(this)">
                        <div class="form-text">
                            Supported formats: JPEG, PNG, GIF. Max size: 5MB.
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="photoDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="photoDescription" name="description" rows="2" placeholder="Enter photo description..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="uploadPhotoBtn" onclick="uploadPhoto()">
                    <i class="fas fa-upload me-2"></i>Upload Photo
                </button>
            </div>
        </div>
    </div>
</div>