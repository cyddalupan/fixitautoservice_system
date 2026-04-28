@extends('layouts.app')

@section('title', 'Payment ' . $payment->payment_number)

@section('content')
<!-- Service Progress Bar -->
@if($payment->serviceProgress)
    <div class="row mb-4">
        <div class="col-12">
            @include('components.service-progress-bar', [
                'progress' => $payment->serviceProgress,
                'currentStage' => 'payment'
            ])
        </div>
    </div>
@endif

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-credit-card text-primary"></i> Payment #{{ $payment->payment_number }}
            </h1>
            <p class="text-muted mb-0">
                {{ $payment->customer->first_name ?? 'Customer' }} {{ $payment->customer->last_name ?? '' }}
                • {{ $payment->payment_date->format('M d, Y') }}
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Payments
            </a>
            <a href="{{ route('payments.edit', $payment) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Payment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Payment Number:</th>
                                    <td>{{ $payment->payment_number }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $payment->status_color }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ $payment->payment_method_display }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Amount:</th>
                                    <td class="fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Reference Number:</th>
                                    <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $payment->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information Card -->
            @if($payment->invoice)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Invoice Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Invoice Number:</th>
                                    <td>
                                        <a href="{{ route('invoices.show', $payment->invoice) }}">
                                            {{ $payment->invoice->invoice_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Invoice Date:</th>
                                    <td>{{ $payment->invoice->invoice_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>{{ $payment->invoice->due_date->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Invoice Amount:</th>
                                    <td>₱{{ number_format($payment->invoice->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Amount Paid:</th>
                                    <td>₱{{ number_format($payment->invoice->amount_paid, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Balance Due:</th>
                                    <td class="fw-bold {{ $payment->invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                        ₱{{ number_format($payment->invoice->balance_due, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes Card -->
            @if($payment->notes)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sticky-note me-2"></i>Notes
                    </h6>
                </div>
                <div class="card-body">
                    <p>{{ $payment->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Customer Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $payment->customer->first_name ?? '' }} {{ $payment->customer->last_name ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $payment->customer->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $payment->customer->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $payment->customer->address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($payment->status === 'pending')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markCompletedModal">
                                <i class="fas fa-check me-1"></i> Mark as Completed
                            </button>
                        @endif
                        
                        @if($payment->status === 'completed')
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                                <i class="fas fa-undo me-1"></i> Process Refund
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-archive me-1"></i> Archive Payment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li>
                            <div class="timeline-badge bg-primary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title">Payment Created</h6>
                                    <p class="text-muted">
                                        <small>{{ $payment->created_at->format('M d, Y h:i A') }}</small>
                                    </p>
                                </div>
                            </div>
                        </li>
                        @if($payment->status === 'completed')
                        <li class="timeline-inverted">
                            <div class="timeline-badge bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h6 class="timeline-title">Payment Completed</h6>
                                    <p class="text-muted">
                                        <small>{{ $payment->updated_at->format('M d, Y h:i A') }}</small>
                                    </p>
                                </div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Completed Modal -->
<div class="modal fade" id="markCompletedModal" tabindex="-1" aria-labelledby="markCompletedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markCompletedModalLabel">Mark Payment as Completed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payments.mark-as-completed', $payment) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="completed">
                <div class="modal-body">
                    <p>Are you sure you want to mark this payment as completed?</p>
                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number (Optional)</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number" 
                               value="{{ $payment->reference_number }}" placeholder="Enter reference number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payments.refund', $payment) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="refunded">
                <div class="modal-body">
                    <p>Are you sure you want to process a refund for this payment?</p>
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount</label>
                        <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                               value="{{ $payment->amount }}" step="0.01" min="0" max="{{ $payment->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="refund_notes" class="form-label">Refund Notes</label>
                        <textarea class="form-control" id="refund_notes" name="refund_notes" rows="3" 
                                  placeholder="Enter refund reason"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-archive me-2"></i>Archive Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payments.destroy', $payment) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-archive me-2"></i>Are you sure you want to move this payment to archive?
                    </div>
                    <p class="mb-0 text-muted small">The record will be preserved in the archive and can be restored later.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-archive me-1"></i> Move to Archive
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection