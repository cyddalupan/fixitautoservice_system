@extends('layouts.app')

@section('title', $customer->first_name . ' ' . $customer->last_name . ' - Fix-It Auto Services')

@section('styles')
<style>
    .profile-picture-container:hover .btn {
        opacity: 1;
    }
    
    .profile-picture-container .btn {
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }
    
    .customer-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 120px;
        height: 120px;
        font-size: 48px;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
    }
</style>
@endsection

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
                    <!-- Profile Picture Upload Section -->
                    <div class="profile-picture-container position-relative mx-auto mb-3" style="width: 120px; height: 120px;">
                        @if($customer->hasProfilePicture)
                            <img src="{{ $customer->avatar }}" 
                                 alt="{{ $customer->first_name }} {{ $customer->last_name }}"
                                 class="rounded-circle img-fluid border"
                                 style="width: 120px; height: 120px; object-fit: cover;"
                                 id="profile-picture-img">
                        @else
                            <div class="customer-avatar rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width: 120px; height: 120px; font-size: 48px; background-color: #007bff; color: white;"
                                 id="profile-picture-initials">
                                {{ $customer->avatar }}
                            </div>
                        @endif
                        
                        <!-- Upload Button (like Facebook) -->
                        <button type="button" 
                                class="btn btn-primary btn-sm rounded-circle position-absolute"
                                style="bottom: 5px; right: 5px; width: 36px; height: 36px;"
                                data-bs-toggle="modal" 
                                data-bs-target="#profilePictureModal"
                                title="Update profile picture">
                            <i class="fas fa-camera"></i>
                        </button>
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
                    <!-- 1. Schedule Appointment -->
                    <a href="{{ route('appointments.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline-warning">
                        <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                    </a>
                    
                    <!-- 2. Create Repair Order -->
                    <a href="{{ route('inspections.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline-primary">
                        <i class="fas fa-tools me-2"></i> Create Repair Order
                    </a>
                    
                    <!-- 3. Create Estimate -->
                    <a href="{{ route('estimates.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline-info">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Create Estimate
                    </a>
                    
                    <!-- 4. Create Work Order -->
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Vehicles</h6>
                <a href="{{ route('vehicles.create') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Vehicle
                </a>
            </div>
            <div class="card-body">
                @if($customer->vehicles && $customer->vehicles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Brand/Model</th>
                                    <th>Year</th>
                                    <th>License Plate</th>
                                    <th>VIN</th>
                                    <th>Engine No.</th>
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
                                            <small class="text-muted">{{ $vehicle->engine_no ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            @if($vehicle->last_service_date)
                                                <small>{{ $vehicle->last_service_date->format('M j, Y') }}</small>
                                            @else
                                                <small class="text-muted">Never</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-primary">
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
                    <a href="{{ route('vehicles.create') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Add Vehicle
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Recent Service History -->
        <!-- Lead Source: Quotation History -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-invoice-dollar"></i> Lead Source: Quotation Form</h6>
                @if($customer->quotations && $customer->quotations->count() > 1)
                    <span class="badge bg-info">{{ $customer->quotations->count() }} quotations</span>
                @endif
            </div>
            <div class="card-body">
                @if($customer->quotations && $customer->quotations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Concern</th>
                                    <th>Budget</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->quotations as $quotation)
                                    @php
                                        $statusLabels = [
                                            'new_lead' => 'New Lead',
                                            'contacted' => 'Contacted',
                                            'converted_to_customer' => 'Converted to Customer',
                                            'appointment_booked' => 'Appointment Booked',
                                            'won' => 'Won',
                                            'lost' => 'Lost',
                                            'archived' => 'Archived',
                                        ];
                                        $statusColors = [
                                            'new_lead' => 'warning',
                                            'contacted' => 'info',
                                            'converted_to_customer' => 'success',
                                            'appointment_booked' => 'primary',
                                            'won' => 'success',
                                            'lost' => 'danger',
                                            'archived' => 'secondary',
                                        ];
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">{{ $quotation->created_at->format('M j, Y') }}</td>
                                        <td>
                                            <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $quotation->service_description }}">
                                                {{ Str::limit($quotation->service_description, 60) }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($quotation->budget_min || $quotation->budget_max)
                                                @if($quotation->budget_min && $quotation->budget_max)
                                                    ₱{{ number_format($quotation->budget_min) }} - ₱{{ number_format($quotation->budget_max) }}
                                                @elseif($quotation->budget_min)
                                                    ₱{{ number_format($quotation->budget_min) }}+ min
                                                @else
                                                    up to ₱{{ number_format($quotation->budget_max) }}
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $statusColors[$quotation->status] ?? 'secondary' }}">
                                                {{ $statusLabels[$quotation->status] ?? ucfirst($quotation->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-sm btn-outline-primary" title="View Quotation #{{ $quotation->id }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($customer->quotations->count() > 5)
                        <div class="text-center mt-2">
                            <a href="{{ route('quotations.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-secondary">
                                View All {{ $customer->quotations->count() }} Quotations
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No quotation history for this customer.</p>
                    </div>
                @endif
            </div>
        </div>

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

<!-- Profile Picture Upload Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePictureModalLabel">Update Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profilePictureForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Choose a new profile picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" required>
                        <div class="form-text">Supported formats: JPEG, PNG, GIF. Max size: 2MB</div>
                    </div>
                    
                    <!-- Preview -->
                    <div class="mb-3 text-center">
                        <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded d-none" style="max-height: 200px;">
                    </div>
                    
                    @if($customer->hasProfilePicture)
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-danger w-100" id="removeProfilePictureBtn">
                            <i class="fas fa-trash me-2"></i> Remove Current Picture
                        </button>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="uploadProfilePictureBtn">Upload Picture</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        console.log('=== CUSTOMER PROFILE PAGE LOADED ===');
        console.log('Testing upload functionality...');
        
        // Check basic requirements
        if (typeof jQuery === 'undefined') {
            console.error('CRITICAL ERROR: jQuery is not loaded!');
            alert('ERROR: jQuery is not loaded. Page functionality will be broken.');
            return;
        }
        
        console.log('✓ jQuery loaded, version:', $.fn.jquery);
        
        // Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error('WARNING: Bootstrap is not loaded');
        } else {
            console.log('✓ Bootstrap loaded');
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        console.log('✓ Tooltips initialized');

        // Image preview functionality
        $('#profile_picture').change(function() {
            console.log('File input changed');
            const file = this.files[0];
            if (file) {
                console.log('File selected:', file.name, '(', file.size, 'bytes,', file.type, ')');
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).removeClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
        console.log('✓ Image preview handler attached');

        // CRITICAL TEST: Check if upload button exists and is clickable
        const uploadButton = $('#uploadProfilePictureBtn');
        console.log('Upload button check:');
        console.log('  - Selector: #uploadProfilePictureBtn');
        console.log('  - Found:', uploadButton.length, 'element(s)');
        console.log('  - HTML:', uploadButton.length > 0 ? uploadButton[0].outerHTML : 'NOT FOUND');
        
        if (uploadButton.length === 0) {
            console.error('ERROR: Upload button not found! The button might have a different ID or might not exist in the DOM.');
            alert('ERROR: Upload button not found. Please check the page HTML.');
            return;
        }
        
        console.log('✓ Upload button found in DOM');
        
        // Remove ANY existing click handlers first (clean slate)
        uploadButton.off('click');
        
        uploadButton.on("click", function(e) {
        console.log('Please click the "Upload Picture" button to test if click events work.');
            e.preventDefault(); // Prevent default form submission
            
            console.log('=== UPLOAD BUTTON CLICKED ===');
            console.log('Button clicked event fired');
            
            // Check if file is selected
            const fileInput = $('#profile_picture')[0];
            console.log('File input element:', fileInput);
            console.log('File input files:', fileInput.files);
            console.log('File input value:', fileInput.value);
            
            if (!fileInput.files || fileInput.files.length === 0) {
                console.log('ERROR: No file selected');
                showToast('error', 'Please select a picture to upload.');
                return;
            }
            
            console.log('File selected:', fileInput.files[0].name, fileInput.files[0].size, 'bytes');
            
            // Disable button to prevent multiple clicks
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');
            
            const formData = new FormData($('#profilePictureForm')[0]);
            const customerId = {{ $customer->id }};
            const uploadUrl = '{{ route("customers.upload-profile-picture", $customer) }}';
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            console.log('=== UPLOAD DETAILS ===');
            console.log('Upload URL:', uploadUrl);
            console.log('CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
            console.log('Customer ID:', customerId);
            console.log('Form data entries:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + (pair[0] === 'profile_picture' ? '[FILE]' : pair[1]));
            }
            
            console.log('=== STARTING AJAX REQUEST ===');
            
            $.ajax({
                url: uploadUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response, status, xhr) {
                    console.log('=== AJAX SUCCESS RESPONSE ===');
                    console.log('Response status:', xhr.status);
                    console.log('Response data:', response);
                    
                    if (response.success) {
                        console.log('Upload successful!');
                        // Update profile picture on page
                        if ($('#profile-picture-img').length) {
                            $('#profile-picture-img').attr('src', response.profile_picture_url + '?' + new Date().getTime());
                        } else {
                            // Replace initials with image
                            $('#profile-picture-initials').replaceWith(
                                '<img src="' + response.profile_picture_url + '" ' +
                                'alt="{{ $customer->first_name }} {{ $customer->last_name }}" ' +
                                'class="rounded-circle img-fluid border" ' +
                                'style="width: 120px; height: 120px; object-fit: cover;" ' +
                                'id="profile-picture-img">'
                            );
                        }
                        
                        // Show success message
                        showToast('success', 'Profile picture updated successfully!');
                        
                        // Close modal
                        $('#profilePictureModal').modal('hide');
                        $('#profilePictureForm')[0].reset();
                        $('#imagePreview').addClass('d-none').attr('src', '#');
                    } else {
                        console.log('Upload returned success:false');
                        showToast('error', response.message || 'Upload failed');
                    }
                    
                    // Re-enable button
                    $btn.prop('disabled', false).html('Upload Picture');
                },
                error: function(xhr, status, error) {
                    console.log('=== AJAX ERROR ===');
                    console.log('Status:', status);
                    console.log('Error:', error);
                    console.log('XHR object:', xhr);
                    console.log('Response text:', xhr.responseText);
                    console.log('Response JSON:', xhr.responseJSON);
                    console.log('Status code:', xhr.status);
                    console.log('Status text:', xhr.statusText);
                    
                    let errorMessage = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error or CORS issue. Check if you are logged in.';
                    } else if (xhr.status === 401) {
                        errorMessage = 'You need to be logged in to upload pictures.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to upload pictures.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Upload endpoint not found.';
                    } else if (xhr.status === 413) {
                        errorMessage = 'File too large. Maximum size is 2MB.';
                    } else if (xhr.status === 422) {
                        errorMessage = 'Validation error. Please check the file format and size.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    }
                    
                    console.log('Displaying error:', errorMessage);
                    showToast('error', errorMessage);
                    
                    // Re-enable button
                    $btn.prop('disabled', false).html('Upload Picture');
                }
            });
        });

        // Remove profile picture
        $('#removeProfilePictureBtn').click(function() {
            if (!confirm('Are you sure you want to remove the profile picture?')) {
                return;
            }

            $.ajax({
                url: '{{ route("customers.remove-profile-picture", $customer) }}',
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Replace image with initials
                        const initials = '{{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}';
                        $('#profile-picture-img').replaceWith(
                            '<div class="customer-avatar rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" ' +
                            'style="width: 120px; height: 120px; font-size: 48px; background-color: #007bff; color: white;" ' +
                            'id="profile-picture-initials">' + initials + '</div>'
                        );
                        
                        // Hide remove button
                        $('#removeProfilePictureBtn').remove();
                        
                        // Show success message
                        showToast('success', 'Profile picture removed successfully!');
                        
                        // Close modal
                        $('#profilePictureModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    showToast('error', 'Failed to remove profile picture. Please try again.');
                }
            });
        });

        // Toast notification function
        function showToast(type, message) {
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            const toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
            }
            
            $('#toast-container').append(toastHtml);
            const toastElement = $('#toast-container .toast:last-child');
            const toast = new bootstrap.Toast(toastElement[0]);
            toast.show();
            
            // Remove toast after it hides
            toastElement.on('hidden.bs.toast', function () {
                $(this).remove();
            });
        }
        
        // SIMPLE TEST: Check if button is clickable
        console.log('=== SIMPLE UPLOAD BUTTON TEST ===');
        const testBtn = $('#uploadProfilePictureBtn');
        console.log('Button found:', testBtn.length > 0);
        
        if (testBtn.length > 0) {
            console.log('Button HTML:', testBtn[0].outerHTML);
            
            // Remove any existing handlers and add simple test
            testBtn.off('click.test').on('click.test', function(e) {
                console.log('TEST: Button clicked!');
                alert('TEST SUCCESS: Button is clickable!');
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            });
            
            console.log('Test handler added. Click the upload button to test.');
        } else {
            console.error('ERROR: Button not found!');
        }
    });
</script>
@endsection