@extends('service_sections.base')

@section('section_content')
<!-- Appointments List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Appointments List
                    <span class="badge bg-light text-dark ms-2">{{ count($sectionData) }} appointment(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($sectionData) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment Date</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionData as $appointment)
                                    <tr>
                                        <td>
                                            <strong>{{ $appointment['date']->format('M d, Y') }}</strong><br>
                                            <small class="text-muted">{{ $appointment['date']->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $appointment['customer']->first_name }} {{ $appointment['customer']->last_name }}</strong><br>
                                            <small class="text-muted">{{ $appointment['customer']->phone }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $appointment['vehicle']->year }} {{ $appointment['vehicle']->make }} {{ $appointment['vehicle']->model }}</strong><br>
                                            <small class="text-muted">{{ $appointment['vehicle']->license_plate }}</small>
                                        </td>
                                        <td>{{ $appointment['service_type'] ?? 'General Service' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $appointment['status'] == 'completed' ? 'success' : ($appointment['status'] == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($appointment['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(!empty($appointment['notes']))
                                                <small class="text-muted">{{ Str::limit($appointment['notes'], 50) }}</small>
                                            @else
                                                <span class="text-muted">No notes</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('appointments.show', $appointment['id']) }}" class="btn btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('appointments.edit', $appointment['id']) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteAppointmentModal{{ $appointment['id'] }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h4>No Appointments Found</h4>
                        <p class="text-muted">No appointments match your current filters.</p>
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create New Appointment
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Complete Workflows (for context) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-secondary">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Complete Service Workflows
                    <span class="badge bg-light text-dark ms-2">{{ count($workflows) }} vehicle(s) with service history</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($workflows) > 0)
                    @foreach($workflows as $workflow)
                        <div class="card mb-4 border shadow-sm">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-car me-2"></i>
                                            {{ $workflow['vehicle']->year }} {{ $workflow['vehicle']->make }} {{ $workflow['vehicle']->model }}
                                            <small class="text-muted">({{ $workflow['vehicle']->license_plate }})</small>
                                        </h6>
                                        <small class="text-muted">
                                            Customer: {{ $workflow['customer']->first_name }} {{ $workflow['customer']->last_name }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($workflow['has_any_transaction'])
                                            <span class="badge bg-success">Active Service</span>
                                        @else
                                            <span class="badge bg-secondary">No Active Service</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Workflow Timeline -->
                                <div class="row text-center">
                                    <div class="col">
                                        <div class="p-3 border rounded {{ !empty($workflow['appointments']) ? 'bg-primary text-white' : 'bg-light' }}">
                                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                            <h6>Appointment</h6>
                                            @if(!empty($workflow['appointments']))
                                                <small>{{ $workflow['appointments'][0]->date->format('M d') }}</small>
                                            @else
                                                <small class="text-muted">Not Scheduled</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3 border rounded {{ !empty($workflow['inspections']) ? 'bg-warning text-dark' : 'bg-light' }}">
                                            <i class="fas fa-tools fa-2x mb-2"></i>
                                            <h6>Repair Order</h6>
                                            @if(!empty($workflow['inspections']))
                                                <small>Inspection Done</small>
                                            @else
                                                <small class="text-muted">Pending</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3 border rounded {{ !empty($workflow['estimates']) ? 'bg-success text-white' : 'bg-light' }}">
                                            <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
                                            <h6>Estimate</h6>
                                            @if(!empty($workflow['estimates']))
                                                <small>
                                                    @foreach($workflow['estimates'] as $estimate)
                                                        {{ $estimate->estimate_number }}<br>
                                                    @endforeach
                                                </small>
                                            @else
                                                <small class="text-muted">Not Created</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3 border rounded {{ !empty($workflow['work_orders']) ? 'bg-danger text-white' : 'bg-light' }}">
                                            <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                            <h6>Job Order</h6>
                                            @if(!empty($workflow['work_orders']))
                                                <small>Work Order Created</small>
                                            @else
                                                <small class="text-muted">Pending</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3 border rounded {{ !empty($workflow['payments']) ? 'bg-purple text-white' : 'bg-light' }}">
                                            <i class="fas fa-credit-card fa-2x mb-2"></i>
                                            <h6>Payment</h6>
                                            @if(!empty($workflow['payments']))
                                                <small>Payment Received</small>
                                            @else
                                                <small class="text-muted">Pending</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h4>No Service Workflows Found</h4>
                        <p class="text-muted">No vehicles have complete service workflows yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($sectionData as $appointment)
<div class="modal fade" id="deleteAppointmentModal{{ $appointment['id'] }}" tabindex="-1" aria-labelledby="deleteAppointmentModalLabel{{ $appointment['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAppointmentModalLabel{{ $appointment['id'] }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>Are you sure you want to delete this appointment?</p>
                
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Appointment Details:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Date:</strong> {{ $appointment['date']->format('M d, Y h:i A') }}</li>
                            <li><strong>Customer:</strong> {{ $appointment['customer']->first_name }} {{ $appointment['customer']->last_name }}</li>
                            <li><strong>Vehicle:</strong> {{ $appointment['vehicle']->year }} {{ $appointment['vehicle']->make }} {{ $appointment['vehicle']->model }} ({{ $appointment['vehicle']->license_plate }})</li>
                            <li><strong>Service Type:</strong> {{ $appointment['service_type'] ?? 'General Service' }}</li>
                            <li><strong>Status:</strong> <span class="badge bg-{{ $appointment['status'] == 'completed' ? 'success' : ($appointment['status'] == 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($appointment['status']) }}</span></li>
                            @if(!empty($appointment['notes']))
                            <li><strong>Notes:</strong> {{ Str::limit($appointment['notes'], 100) }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This record will be moved to the Archive where it can be restored if needed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form action="{{ route('appointments.destroy', $appointment['id']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Appointment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection