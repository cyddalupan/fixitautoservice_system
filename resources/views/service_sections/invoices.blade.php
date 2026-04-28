@extends('service_sections.base')

@section('section_content')
<!-- Invoices & Payments List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-purple">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0">
                    <i class="fas fa-credit-card me-2"></i>Invoices & Payments List
                    <span class="badge bg-light text-dark ms-2">{{ count($sectionData) }} payment(s)</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($sectionData) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionData as $payment)
                                    <tr>
                                        <td>
                                            <strong>{{ $payment['date']->format('M d, Y') }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $payment['customer']->first_name }} {{ $payment['customer']->last_name }}</strong><br>
                                            <small class="text-muted">{{ $payment['customer']->phone }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $payment['vehicle']->year }} {{ $payment['vehicle']->make }} {{ $payment['vehicle']->model }}</strong><br>
                                            <small class="text-muted">{{ $payment['vehicle']->license_plate }}</small>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($payment['amount'] ?? 0, 2) }}</strong>
                                        </td>
                                        <td>{{ $payment['payment_method'] ?? 'Not specified' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment['status'] == 'paid' ? 'success' : ($payment['status'] == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($payment['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('invoices.show', $payment['id']) }}" class="btn btn-outline-purple" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('invoices.edit', $payment['id']) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteInvoiceModal{{ $payment['id'] }}">
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
                        <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                        <h4>No Payments Found</h4>
                        <p class="text-muted">No payments match your current filters.</p>
                        <a href="{{ route('invoices.create') }}" class="btn btn-purple">
                            <i class="fas fa-plus me-1"></i> Create New Invoice
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
@foreach($sectionData as $payment)
<div class="modal fade" id="deleteInvoiceModal{{ $payment['id'] }}" tabindex="-1" aria-labelledby="deleteInvoiceModalLabel{{ $payment['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteInvoiceModalLabel{{ $payment['id'] }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p>Are you sure you want to delete this invoice/payment?</p>
                
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-danger">Invoice/Payment Details:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Date:</strong> {{ $payment['date']->format('M d, Y') }}</li>
                            <li><strong>Customer:</strong> {{ $payment['customer']->first_name }} {{ $payment['customer']->last_name }}</li>
                            <li><strong>Vehicle:</strong> {{ $payment['vehicle']->year }} {{ $payment['vehicle']->make }} {{ $payment['vehicle']->model }} ({{ $payment['vehicle']->license_plate }})</li>
                            <li><strong>Amount:</strong> ${{ number_format($payment['amount'] ?? 0, 2) }}</li>
                            <li><strong>Payment Method:</strong> {{ $payment['payment_method'] ?? 'Not specified' }}</li>
                            <li><strong>Status:</strong> <span class="badge bg-{{ $payment['status'] == 'paid' ? 'success' : ($payment['status'] == 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($payment['status']) }}</span></li>
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
                <form action="{{ route('invoices.destroy', $payment['id']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Invoice/Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection