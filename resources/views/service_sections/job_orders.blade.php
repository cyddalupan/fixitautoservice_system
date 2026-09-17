@extends('service_sections.base')

@section('section_content')
<!-- Job Orders List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>Job Orders List
                    <span class="badge bg-light text-dark ms-2">{{ count($sectionData) }} job order(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($sectionData) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionData as $jobOrder)
                                    <tr>
                                        <td>
                                            <strong>{{ $jobOrder['date']->format('M d, Y') }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $jobOrder['customer']->first_name }} {{ $jobOrder['customer']->last_name }}</strong><br>
                                            <small class="text-muted">{{ $jobOrder['customer']->phone }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $jobOrder['vehicle']->year }} {{ $jobOrder['vehicle']->make }} {{ $jobOrder['vehicle']->model }}</strong><br>
                                            <small class="text-muted">{{ $jobOrder['vehicle']->license_plate }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $jobOrder['status'] == 'completed' ? 'success' : ($jobOrder['status'] == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($jobOrder['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('job-orders.show', $jobOrder['id']) }}" class="btn btn-outline-danger" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('job-orders.edit', $jobOrder['id']) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteJobOrderModal{{ $jobOrder['id'] }}">
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
                        <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                        <h4>No Job Orders Found</h4>
                        <p class="text-muted">No job orders match your current filters.</p>
                        <a href="{{ route('job-orders.create') }}" class="btn btn-danger">
                            <i class="fas fa-plus me-1"></i> Create New Job Order
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
@foreach($sectionData as $jobOrder)
<div class="modal fade" id="deleteJobOrderModal{{ $jobOrder['id'] }}" tabindex="-1" aria-labelledby="deleteJobOrderModalLabel{{ $jobOrder['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteJobOrderModalLabel{{ $jobOrder['id'] }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>Are you sure you want to delete this job order?</p>
                
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Job Order Details:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Date:</strong> {{ $jobOrder['date']->format('M d, Y') }}</li>
                            <li><strong>Customer:</strong> {{ $jobOrder['customer']->first_name }} {{ $jobOrder['customer']->last_name }}</li>
                            <li><strong>Vehicle:</strong> {{ $jobOrder['vehicle']->year }} {{ $jobOrder['vehicle']->make }} {{ $jobOrder['vehicle']->model }} ({{ $jobOrder['vehicle']->license_plate }})</li>
                            <li><strong>Status:</strong> <span class="badge bg-{{ $jobOrder['status'] == 'completed' ? 'success' : ($jobOrder['status'] == 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($jobOrder['status']) }}</span></li>
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
                <form action="{{ route('job-orders.destroy', $jobOrder['id']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Job Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection