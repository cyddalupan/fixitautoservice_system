@extends('layouts.app')

@section('title', 'Create New Audit')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Create New Quality Audit</h1>
            <p class="text-muted">Schedule and configure a new quality control audit</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Audits
            </a>
        </div>
    </div>

    <!-- Audit Creation Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Audit Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('audit.store') }}" id="auditForm">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label">Audit Title *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">e.g., "Monthly Brake Service Audit"</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="audit_date" class="form-label">Audit Date *</label>
                                    <input type="date" class="form-control @error('audit_date') is-invalid @enderror" 
                                           id="audit_date" name="audit_date" value="{{ old('audit_date', date('Y-m-d')) }}" required>
                                    @error('audit_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Selection -->
                        <div class="form-group mb-4">
                            <label for="checklist_id" class="form-label">Checklist *</label>
                            <select class="form-control @error('checklist_id') is-invalid @enderror" 
                                    id="checklist_id" name="checklist_id" required>
                                <option value="">Select a checklist</option>
                                @foreach($checklists as $checklist)
                                    <option value="{{ $checklist->id }}" 
                                            {{ old('checklist_id') == $checklist->id ? 'selected' : '' }}
                                            data-service-type="{{ $checklist->service_type }}">
                                        {{ $checklist->name }} ({{ str_replace('_', ' ', ucfirst($checklist->service_type)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('checklist_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Personnel Assignment -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="technician_id" class="form-label">Technician *</label>
                                    <select class="form-control @error('technician_id') is-invalid @enderror" 
                                            id="technician_id" name="technician_id" required>
                                        <option value="">Select technician</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}" 
                                                    {{ old('technician_id') == $technician->id ? 'selected' : '' }}>
                                                {{ $technician->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('technician_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="auditor_id" class="form-label">Auditor *</label>
                                    <select class="form-control @error('auditor_id') is-invalid @enderror" 
                                            id="auditor_id" name="auditor_id" required>
                                        <option value="">Select auditor</option>
                                        @foreach($auditors as $auditor)
                                            <option value="{{ $auditor->id }}" 
                                                    {{ old('auditor_id') == $auditor->id ? 'selected' : '' }}>
                                                {{ $auditor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('auditor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_id" class="form-label">Vehicle (Optional)</label>
                                    <select class="form-control @error('vehicle_id') is-invalid @enderror" 
                                            id="vehicle_id" name="vehicle_id">
                                        <option value="">Select vehicle</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" 
                                                    {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->license_plate }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="work_order_id" class="form-label">Work Order (Optional)</label>
                                    <select class="form-control @error('work_order_id') is-invalid @enderror" 
                                            id="work_order_id" name="work_order_id">
                                        <option value="">Select work order</option>
                                        @foreach($workOrders as $workOrder)
                                            <option value="{{ $workOrder->id }}" 
                                                    {{ old('work_order_id') == $workOrder->id ? 'selected' : '' }}>
                                                WO-{{ $workOrder->id }} - {{ $workOrder->customer->name ?? 'Unknown' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('work_order_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional notes about this audit</small>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-4">
                            <label for="status" class="form-label">Initial Status *</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Audit
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="previewAudit()">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <button type="reset" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar: Checklist Preview -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Checklist Preview</h6>
                </div>
                <div class="card-body">
                    <div id="checklistPreview" class="text-center text-muted">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <p>Select a checklist to preview items</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="checklistCount">0</div>
                                <div class="text-xs text-muted">Checklists</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="technicianCount">{{ $technicians->count() }}</div>
                                <div class="text-xs text-muted">Technicians</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="auditorCount">{{ $auditors->count() }}</div>
                                <div class="text-xs text-muted">Auditors</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="vehicleCount">{{ $vehicles->count() }}</div>
                                <div class="text-xs text-muted">Vehicles</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Tips -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Tips for Effective Audits</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Schedule audits during normal business hours</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Choose appropriate checklists for service type</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Assign experienced auditors for complex services</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Link to work orders for better context</small>
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <small>Add detailed descriptions for audit scope</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Audit Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">
                    <i class="fas fa-check"></i> Confirm & Create
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update checklist count
    document.getElementById('checklistCount').textContent = {{ $checklists->count() }};
    
    // Load checklist preview
    document.getElementById('checklist_id').addEventListener('change', function() {
        const checklistId = this.value;
        
        if (!checklistId) {
            document.getElementById('checklistPreview').innerHTML = `
                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                <p>Select a checklist to preview items</p>
            `;
            return;
        }
        
        fetch(`/quality-control/checklists/${checklistId}/preview`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const items = data.items;
                    let html = `
                        <h6 class="font-weight-bold">${data.name}</h6>
                        <p class="text-muted mb-3">${data.service_type}</p>
                        <div class="text-left">
                    `;
                    
                    items.forEach((item, index) => {
                        html += `
                            <div class="mb-2">
                                <small>
                                    <i class="fas fa-check-circle text-success mr-1"></i>
                                    ${item.description}
                                </small>
                            </div>
                        `;
                    });
                    
                    html += `
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Items: ${items.length}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Passing: ${data.passing_score}%</small>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('checklistPreview').innerHTML = html;
                }
            })
            .catch(error => {
                document.getElementById('checklistPreview').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Unable to load checklist preview
                    </div>
                `;
            });
    });
    
    // Preview audit
    function previewAudit() {
        const form = document.getElementById('auditForm');
        const formData = new FormData(form);
        
        // Basic validation
        const requiredFields = ['title', 'audit_date', 'checklist_id', 'technician_id', 'auditor_id', 'status'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!formData.get(field)) {
                isValid = false;
                document.querySelector(`[name="${field}"]`).classList.add('is-invalid');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields.');
            return;
        }
        
        // Build preview
        const previewContent = document.getElementById('previewContent');
        previewContent.innerHTML = `
            <div class="preview-section mb-4">
                <h6>Audit Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Title:</strong></td><td>${formData.get('title')}</td></tr>
                    <tr><td><strong>Date:</strong></td><td>${formData.get('audit_date')}</td></tr>
                    <tr><td><strong>Status:</strong></td><td>${formData.get('status')}</td></tr>
                    ${formData.get('description') ? `<tr><td><strong>Description:</strong></td><td>${formData.get('description')}</td></tr>` : ''}
                </table>
            </div>
            
            <div class="preview-section mb-4">
                <h6>Personnel</h6>
                <table class="table table-sm">
                    <tr><td><strong>Technician:</strong></td><td>${document.querySelector('#technician_id option:checked').textContent}</td></tr>
                    <tr><td><strong>Auditor:</strong></td><td>${document.querySelector('#auditor_id option:checked').textContent}</td></tr>
                </table>
            </div>
            
            <div class="preview-section mb-4">
                <h6>Checklist</h6>
                <table class="table table-sm">
                    <tr><td><strong>Checklist:</strong></td><td>${document.querySelector('#checklist_id option:checked').textContent}</td></tr>
                </table>
            </div>
            
            ${formData.get('vehicle_id') ? `
            <div class="preview-section mb-4">
                <h6>Vehicle</h6>
                <table class="table table-sm">
                    <tr><td><strong>Vehicle:</strong></td><td>${document.querySelector('#vehicle_id option:checked').textContent}</td></tr>
                </table>
            </div>
            ` : ''}
            
            ${formData.get('work_order_id') ? `
            <div class="preview-section mb-4">
                <h6>Work Order</h6>
                <table class="table table-sm">
                    <tr><td><strong>Work Order:</strong></td><td>${document.querySelector('#work_order_id option:checked').textContent}</td></tr>
                </table>
            </div>
            ` : ''}
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Review the audit details before creating. Once created, you can add audit results.
            </div>
        `;
        
        $('#previewModal').modal('show');
    }
    
    // Submit form from preview
    function submitForm() {
        document.getElementById('auditForm').submit();
    }
    
    // Set default date to today
    document.getElementById('audit_date').valueAsDate = new Date();
    
    // Auto-select today's date for audit date
    const today = new Date().toISOString().split('T')[0];
    if (!document.getElementById('audit_date').value) {
        document.getElementById('audit_date').value = today;
    }
</script>
@endpush