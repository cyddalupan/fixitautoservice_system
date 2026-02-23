@extends('layouts.app')

@section('title', 'New Service Request - Customer Portal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle me-2"></i>New Service Request
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.service-requests.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Requests
                        </a>
                    </div>
                </div>
                <form action="{{ route('portal.service-requests.store') }}" method="POST" enctype="multipart/form-data" id="serviceRequestForm">
                    @csrf
                    <div class="card-body">
                        <!-- Progress Steps -->
                        <div class="mb-5">
                            <div class="progress-steps">
                                <div class="step active" data-step="1">
                                    <div class="step-circle">1</div>
                                    <div class="step-label">Basic Info</div>
                                </div>
                                <div class="step" data-step="2">
                                    <div class="step-circle">2</div>
                                    <div class="step-label">Vehicle Details</div>
                                </div>
                                <div class="step" data-step="3">
                                    <div class="step-circle">3</div>
                                    <div class="step-label">Service Details</div>
                                </div>
                                <div class="step" data-step="4">
                                    <div class="step-circle">4</div>
                                    <div class="step-label">Attachments</div>
                                </div>
                                <div class="step" data-step="5">
                                    <div class="step-circle">5</div>
                                    <div class="step-label">Review & Submit</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Basic Information -->
                        <div class="step-content" id="step1">
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Basic Information
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="subject" class="form-label">
                                                    <i class="fas fa-heading me-1"></i>Request Subject *
                                                </label>
                                                <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                                       id="subject" name="subject" value="{{ old('subject') }}" 
                                                       placeholder="Brief description of your service request..." required>
                                                @error('subject')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    Be specific about what service you need (e.g., "Brake inspection needed", "Oil change request")
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="priority" class="form-label">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Priority *
                                                </label>
                                                <select class="form-select @error('priority') is-invalid @enderror" 
                                                        id="priority" name="priority" required>
                                                    <option value="">Select priority...</option>
                                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low (No rush)</option>
                                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium (Within 1-2 weeks)</option>
                                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High (Within 1 week)</option>
                                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent (ASAP)</option>
                                                </select>
                                                @error('priority')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    How soon do you need this service?
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-align-left me-1"></i>Detailed Description *
                                        </label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="5" 
                                                  placeholder="Please provide detailed information about the service you need..." required>{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Include symptoms, noises, warning lights, or any other relevant information.
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <span id="charCount">0</span> characters (minimum 50 recommended)
                                            </small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="preferred_date" class="form-label">
                                                    <i class="fas fa-calendar-alt me-1"></i>Preferred Service Date
                                                </label>
                                                <input type="date" class="form-control" 
                                                       id="preferred_date" name="preferred_date"
                                                       min="{{ date('Y-m-d') }}" 
                                                       value="{{ old('preferred_date') }}">
                                                <div class="form-text">
                                                    When would you like to bring your vehicle in?
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="preferred_time" class="form-label">
                                                    <i class="fas fa-clock me-1"></i>Preferred Time
                                                </label>
                                                <select class="form-select" id="preferred_time" name="preferred_time">
                                                    <option value="">Any time</option>
                                                    <option value="morning" {{ old('preferred_time') == 'morning' ? 'selected' : '' }}>Morning (8 AM - 12 PM)</option>
                                                    <option value="afternoon" {{ old('preferred_time') == 'afternoon' ? 'selected' : '' }}>Afternoon (12 PM - 4 PM)</option>
                                                    <option value="evening" {{ old('preferred_time') == 'evening' ? 'selected' : '' }}>Evening (4 PM - 6 PM)</option>
                                                </select>
                                                <div class="form-text">
                                                    Your preferred time of day
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Vehicle Selection -->
                        <div class="step-content" id="step2" style="display: none;">
                            <div class="card border-success mb-4">
                                <div class="card-header bg-success text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-car me-2"></i>Vehicle Selection
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Select the vehicle that needs service. If your vehicle isn't listed, you can add it to your profile first.
                                    </div>

                                    <div class="row">
                                        @foreach($vehicles as $vehicle)
                                            <div class="col-md-6 mb-3">
                                                <div class="card vehicle-card {{ old('vehicle_id') == $vehicle->id ? 'border-primary' : '' }}" 
                                                     onclick="selectVehicle({{ $vehicle->id }})">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-car fa-3x text-muted"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="card-title mb-1">
                                                                    {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                                                </h5>
                                                                <p class="card-text mb-1">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-tag me-1"></i>License: {{ $vehicle->license_plate }}
                                                                    </small>
                                                                </p>
                                                                <p class="card-text mb-0">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-barcode me-1"></i>VIN: {{ $vehicle->vin }}
                                                                    </small>
                                                                </p>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" 
                                                                           name="vehicle_id" value="{{ $vehicle->id }}" 
                                                                           id="vehicle_{{ $vehicle->id }}"
                                                                           {{ old('vehicle_id') == $vehicle->id ? 'checked' : '' }}>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($vehicles->count() == 0)
                                        <div class="text-center py-4">
                                            <i class="fas fa-car fa-4x text-muted mb-3"></i>
                                            <h4>No Vehicles Found</h4>
                                            <p class="text-muted mb-4">
                                                You need to add a vehicle to your profile before creating a service request.
                                            </p>
                                            <a href="{{ route('portal.vehicles.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Add Vehicle
                                            </a>
                                        </div>
                                    @endif

                                    <div class="mt-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="no_vehicle" name="no_vehicle" value="1">
                                            <label class="form-check-label" for="no_vehicle">
                                                This service request is not for a specific vehicle (e.g., general inquiry, parts request)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Service Details -->
                        <div class="step-content" id="step3" style="display: none;">
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-tools me-2"></i>Service Details
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h5 class="mb-3">Service Categories</h5>
                                        <div class="row">
                                            @foreach($serviceCategories as $category)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card service-category-card" 
                                                         onclick="toggleCategory('{{ $category['id'] }}')">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i class="{{ $category['icon'] }} fa-2x text-primary"></i>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="card-title mb-1">{{ $category['name'] }}</h6>
                                                                    <p class="card-text small text-muted mb-0">{{ $category['description'] }}</p>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input category-checkbox" 
                                                                               type="checkbox" 
                                                                               name="service_categories[]" 
                                                                               value="{{ $category['id'] }}"
                                                                               id="category_{{ $category['id'] }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h5 class="mb-3">Specific Services</h5>
                                        <div id="specificServicesContainer">
                                            <!-- Dynamic content will be loaded here based on selected categories -->
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Select service categories above to see specific services.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="estimated_budget" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>Estimated Budget (Optional)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" 
                                                   id="estimated_budget" name="estimated_budget" 
                                                   min="0" step="0.01" placeholder="0.00"
                                                   value="{{ old('estimated_budget') }}">
                                        </div>
                                        <div class="form-text">
                                            Your estimated budget for this service (helps us provide appropriate recommendations)
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="special_instructions" class="form-label">
                                            <i class="fas fa-sticky-note me-1"></i>Special Instructions
                                        </label>
                                        <textarea class="form-control" id="special_instructions" 
                                                  name="special_instructions" rows="3"
                                                  placeholder="Any special instructions or notes...">{{ old('special_instructions') }}</textarea>
                                        <div class="form-text">
                                            Additional information for our technicians (e.g., "Please call before starting work", "Leave keys with reception")
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Attachments -->
                        <div class="step-content" id="step4" style="display: none;">
                            <div class="card border-warning mb-4">
                                <div class="card-header bg-warning text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-paperclip me-2"></i>Attachments
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Upload photos, videos, or documents that might help us understand your service needs better.
                                    </div>

                                    <div class="mb-4">
                                        <div class="dropzone border rounded p-5 text-center" id="dropzone">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5>Drag & Drop Files Here</h5>
                                            <p class="text-muted">or click to browse</p>
                                            <input type="file" id="fileInput" name="attachments[]" multiple 
                                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" style="display: none;">
                                            <button type="button" class="btn btn-outline-primary mt-2" onclick="document.getElementById('fileInput').click()">
                                                <i class="fas fa-folder-open me-1"></i> Browse Files
                                            </button>
                                            <p class="small text-muted mt-2">
                                                Max file size: 10MB each. Supported formats: Images, PDF, Word, Excel
                                            </p>
                                        </div>
                                    </div>

                                    <div id="fileList" class="mb-4">
                                        <!-- Uploaded files will appear here -->
                                    </div>

                                    <div class="mb-3">
                                        <label for="attachment_notes" class="form-label">
                                            <i class="fas fa-comment me-1"></i>Attachment Notes
                                        </label>
                                        <textarea class="form-control" id="attachment_notes" 
                                                  name="attachment_notes" rows="3"
                                                  placeholder="Notes about your attachments...">{{ old('attachment_notes') }}</textarea>
                                        <div class="form-text">
                                            Describe what each attachment shows or why it's relevant.
                                        </div>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Important:</strong> Do not upload sensitive personal information (SSN, credit card numbers, etc.).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Review & Submit -->
                        <div class="step-content" id="step5" style="display: none;">
                            <div class="card border-danger mb-4">
                                <div class="card-header bg-danger text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-check-circle me-2"></i>Review & Submit
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check me-2"></i>
                                        <strong>Almost done!</strong> Please review your service request before submitting.
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h5><i class="fas fa-info-circle me-2"></i>Request Summary</h5>
                                            <div class="card">
                                                <div class="card-body">
                                                    <p><strong>Subject:</strong> <span id="reviewSubject"></span></p>
                                                    <p><strong>Priority:</