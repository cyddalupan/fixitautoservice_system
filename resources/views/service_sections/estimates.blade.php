@extends('service_sections.base')

@section('section_content')
<!-- Estimates List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Estimates List
                    <span class="badge bg-light text-dark ms-2">{{ count($sectionData) }} estimate(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($sectionData) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Estimate #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionData as $estimate)
                                    <tr>
                                        <td>
                                            <strong>{{ $estimate['estimate_number'] }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $estimate['date']->format('M d, Y') }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $estimate['customer']->first_name }} {{ $estimate['customer']->last_name }}</strong><br>
                                            <small class="text-muted">{{ $estimate['customer']->phone }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $estimate['vehicle']->year }} {{ $estimate['vehicle']->make }} {{ $estimate['vehicle']->model }}</strong><br>
                                            <small class="text-muted">{{ $estimate['vehicle']->license_plate }}</small>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($estimate['total_amount'], 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $estimate['status'] == 'approved' ? 'success' : ($estimate['status'] == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($estimate['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('estimates.show', $estimate['id']) }}" class="btn btn-outline-success" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('estimates.edit', $estimate['id']) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteEstimateModal{{ $estimate['id'] }}">
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
                        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                        <h4>No Estimates Found</h4>
                        <p class="text-muted">No estimates match your current filters.</p>
                        <a href="{{ route('estimates.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Create New Estimate
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
@foreach($sectionData as $estimate)
<div class="modal fade" id="deleteEstimateModal{{ $estimate['id'] }}" tabindex="-1" aria-labelledby="deleteEstimateModalLabel{{ $estimate['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteEstimateModalLabel{{ $estimate['id'] }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>Are you sure you want to delete this estimate?</p>
                
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Estimate Details:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Estimate #:</strong> {{ $estimate['estimate_number'] }}</li>
                            <li><strong>Date:</strong> {{ $estimate['date']->format('M d, Y') }}</li>
                            <li><strong>Customer:</strong> {{ $estimate['customer']->first_name }} {{ $estimate['customer']->last_name }}</li>
                            <li><strong>Vehicle:</strong> {{ $estimate['vehicle']->year }} {{ $estimate['vehicle']->make }} {{ $estimate['vehicle']->model }} ({{ $estimate['vehicle']->license_plate }})</li>
                            <li><strong>Total Amount:</strong> ${{ number_format($estimate['total_amount'], 2) }}</li>
                            <li><strong>Status:</strong> <span class="badge bg-{{ $estimate['status'] == 'approved' ? 'success' : ($estimate['status'] == 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($estimate['status']) }}</span></li>
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
                <form action="{{ route('estimates.destroy', $estimate['id']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Estimate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection