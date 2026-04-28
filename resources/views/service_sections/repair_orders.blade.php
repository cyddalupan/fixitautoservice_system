@extends('service_sections.base')

@section('section_content')
<!-- Repair Orders List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>Repair Orders List
                    <span class="badge bg-light text-dark ms-2">{{ count($sectionData) }} repair order(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($sectionData) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Inspection Date</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Inspection Type</th>
                                    <th>Technician</th>
                                    <th>Customer Concerns</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionData as $inspection)
                                    <tr>
                                        <td>
                                            <strong>{{ $inspection['date']->format('M d, Y') }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $inspection['customer']->first_name }} {{ $inspection['customer']->last_name }}</strong><br>
                                            <small class="text-muted">{{ $inspection['customer']->phone }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $inspection['vehicle']->year }} {{ $inspection['vehicle']->make }} {{ $inspection['vehicle']->model }}</strong><br>
                                            <small class="text-muted">{{ $inspection['vehicle']->license_plate }}</small>
                                        </td>
                                        <td>{{ $inspection['inspection_type'] ?? 'General Inspection' }}</td>
                                        <td>{{ $inspection['technician'] ?? 'Not assigned' }}</td>
                                        <td>
                                            @if(!empty($inspection['customer_concerns']))
                                                <small class="text-muted">{{ Str::limit($inspection['customer_concerns'], 50) }}</small>
                                            @else
                                                <span class="text-muted">No concerns noted</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('inspections.show', $inspection['id']) }}" class="btn btn-outline-warning" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('inspections.edit', $inspection['id']) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteRepairOrderModal{{ $inspection['id'] }}">
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
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h4>No Repair Orders Found</h4>
                        <p class="text-muted">No repair orders match your current filters.</p>
                        <a href="{{ route('inspections.create') }}" class="btn btn-warning">
                            <i class="fas fa-plus me-1"></i> Create New Repair Order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Complete Workflows (for context) -->
@include('service_sections._workflow_timeline')

<!-- Delete Confirmation Modals -->
@foreach($sectionData as $inspection)
<div class="modal fade" id="deleteRepairOrderModal{{ $inspection['id'] }}" tabindex="-1" aria-labelledby="deleteRepairOrderModalLabel{{ $inspection['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteRepairOrderModalLabel{{ $inspection['id'] }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>Are you sure you want to delete this repair order?</p>
                
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Repair Order Details:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Date:</strong> {{ $inspection['date']->format('M d, Y') }}</li>
                            <li><strong>Customer:</strong> {{ $inspection['customer']->first_name }} {{ $inspection['customer']->last_name }}</li>
                            <li><strong>Vehicle:</strong> {{ $inspection['vehicle']->year }} {{ $inspection['vehicle']->make }} {{ $inspection['vehicle']->model }} ({{ $inspection['vehicle']->license_plate }})</li>
                            <li><strong>Inspection Type:</strong> {{ $inspection['inspection_type'] ?? 'General Inspection' }}</li>
                            <li><strong>Technician:</strong> {{ $inspection['technician'] ?? 'Not assigned' }}</li>
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
                <form action="{{ route('inspections.destroy', $inspection['id']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Repair Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection