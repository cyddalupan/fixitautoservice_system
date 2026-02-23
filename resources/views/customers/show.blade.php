@extends('layouts.app')

@section('title', $customer->first_name . ' ' . $customer->last_name . ' - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user me-2"></i>{{ $customer->first_name }} {{ $customer->last_name }}
            </h1>
            <p class="text-muted mb-0">Customer Profile</p>
        </div>
        <div>
            <div class="btn-group">
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Customer Information -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="customer-avatar mx-auto mb-3">
                        {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                    </div>
                    <h5 class="mb-1">{{ $customer->first_name }} {{ $customer->last_name }}</h5>
                    <p class="text-muted mb-2">
                        <span class="badge bg-{{ $customer->is_active ? 'success' : 'danger' }}">
                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge bg-info ms-1">{{ ucfirst($customer->customer_type) }}</span>
                        @if($customer->segment)
                            <span class="badge bg-secondary ms-1">{{ ucfirst($customer->segment) }}</span>
                        @endif
                    </p>
                </div>
                
                <div class="customer-details">
                    <div class="mb-3">
                        <small class="text-muted d-block">Contact Information</small>
                        @if($customer->email)
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-envelope text-muted me-2" style="width: 20px;"></i>
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            </div>
                        @endif
                        @if($customer->phone)
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-phone text-muted me-2" style="width: 20px;"></i>
                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                            </div>
                        @endif
                        @if($customer->preferred_contact_method)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-comment text-muted me-2" style="width: 20px;"></i>
                                <small>Prefers {{ ucfirst($customer->preferred_contact_method) }}</small>
                            </div>
                        @endif
                    </div>
                    
                    @if($customer->address || $customer->city || $customer->state || $customer->zip_code)
                    <div class="mb-3">
                        <small class="text-muted d-block">Address</small>
                        <div class="d-flex align-items-start mb-1">
                            <i class="fas fa-map-marker-alt text-muted me-2 mt-1" style="width: 20px;"></i>
                            <div>
                                @if($customer->address)
                                    <div>{{ $customer->address }}</div>
                                @endif
                                @if($customer->city || $customer->state || $customer->zip_code)
                                    <div>
                                        {{ $customer->city }}{{ $customer->city && $customer->state ? ', ' : '' }}
                                        {{ $customer->state }} {{ $customer->zip_code }}
                                    </div>
                                @endif
                                @if($customer->country)
                                    <div>{{ $customer->country }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($customer->company_name)
                    <div class="mb-3">
                        <small class="text-muted d-block">Company</small>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-building text-muted me-2" style="width: 20px;"></i>
                            <div>
                                <strong>{{ $customer->company_name }}</strong>
                                @if($customer->tax_id)
                                    <div class="text-muted small">Tax ID: {{ $customer->tax_id }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Customer Since</small>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar text-muted me-2" style="width: 20px;"></i>
                            <div>{{ $customer->created_at->format('F j, Y') }}</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Loyalty Points</small>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-star text-warning me-2" style="width: 20px;"></i>
                            <div>
                                <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                                @if($customer->loyalty_points >= 1000)
                                    <span class="badge bg-success ms-2">Gold Member</span>
                                @elseif($customer->loyalty_points >= 500)
                                    <span class="badge bg-primary ms-2">Silver Member</span>
                                @else
                                    <span class="badge bg-secondary ms-2">Bronze Member</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('customers.vehicles', $customer) }}" class="btn btn-outline-primary">
                        <i class="fas fa-car me-2"></i> View Vehicles
                    </a>
                    <a href="{{ route('customers.service-history', $customer) }}" class="btn btn-outline-success">
                        <i class="fas fa-history me-2"></i> Service History
                    </a>
                    <a href="{{ route('customers.notes', $customer) }}" class="btn btn-outline-info">
                        <i class="fas fa-sticky-note me-2"></i> View Notes
                    </a>
                    <a href="{{ route('appointments.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline-warning">
                        <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                    </a>
                    <a href="{{ route('work-orders.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline-danger">
                        <i class="fas fa-wrench me-2"></i> Create Work Order
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-md-8">
        <!-- Customer Notes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Customer Notes</h6>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="fas fa-plus me-1"></i> Add Note
                </button>
            </div>
            <div class="card-body">
                @if($customer->notes)
                    <div class="mb-3">
                        <p class="mb-0">{{ $customer->notes }}</p>
                    </div>
                @else
                    <p class="text-muted mb-0">No notes available for this customer.</p>
                @endif
                
                <!-- Recent Notes -->
                @if($customer->customerNotes && $customer->customerNotes->count() > 0)
                    <hr>
                    <h6 class="mb-3">Recent Notes</h6>
                    @foreach($customer->customerNotes->take(5) as $note)
                        <div class="card mb-2">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">{{ $note->created_at->format('M j, Y g:i A') }}</small>
                                    <small class="text-muted">{{ $note->note_type ?? 'General' }}</small>
                                </div>
                                <p class="mb-0">{{ $note->content }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        
        <!-- Recent Vehicles -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Vehicles</h6>
            </div>
            <div class="card-body">
                @if($customer->vehicles && $customer->vehicles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Make/Model</th>
                                    <th>Year</th>
                                    <th>License Plate</th>
                                    <th>VIN</th>
                                    <th>Last Service</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->vehicles->take(5) as $vehicle)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-car text-primary me-2"></i>
                                                <div>
                                                    <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>
                                                    <div class="text-muted small">{{ $vehicle->trim ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $vehicle->year }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $vehicle->license_plate ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $vehicle->vin ? substr($vehicle->vin, 0, 8) . '...' : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if($vehicle->last_service_date)
                                                <small>{{ $vehicle->last_service_date->format('M j, Y') }}</small>
                                            @else
                                                <small class="text-muted">Never</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($customer->vehicles->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('customers.vehicles', $customer) }}" class="btn btn-sm btn-outline-secondary">
                                View All {{ $customer->vehicles->count() }} Vehicles
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0">No vehicles registered for this customer.</p>
                    <a href="#" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Add Vehicle
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Recent Service History -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Recent Service History</h6>
            </div>
            <div class="card-body">
                @if($customer->serviceRecords && $customer->serviceRecords->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Service Type</th>
                                    <th>Vehicle</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->serviceRecords->take(5) as $record)
                                    <tr>
                                        <td>{{ $record->service_date->format('M j, Y') }}</td>
                                        <td>{{ $record->service_type }}</td>
                                        <td>
                                            <small>{{ $record->vehicle->make ?? 'N/A' }} {{ $record->vehicle->model ?? '' }}</small>
                                        </td>
                                        <td>${{ number_format($record->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $record->status == 'completed' ? 'success' : ($record->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($customer->serviceRecords->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('customers.service-history', $customer) }}" class="btn btn-sm btn-outline-secondary">
                                View All {{ $customer->serviceRecords->count() }} Services
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0">No service history available for this customer.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.notes.store', $customer) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">Add Customer Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note_type" class="form-label">Note Type</label>
                        <select class="form-select" id="note_type" name="note_type">
                            <option value="general">General</option>
                            <option value="preference">Preference</option>
                            <option value="issue">Issue</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Note Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection