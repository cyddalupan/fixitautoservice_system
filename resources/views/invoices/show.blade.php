@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>Invoice {{ $invoice->invoice_number }}
            </h1>
            <p class="text-muted mb-0">
                {{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}
                • Created {{ $invoice->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            
            @if($invoice->status === 'draft')
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
            
            @if($invoice->status === 'sent' && $invoice->payment_status !== 'paid')
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" 
                   class="btn btn-success">
                    <i class="fas fa-money-bill-wave me-1"></i> Receive Payment
                </a>
            @endif
            
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-1"></i> Actions
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('invoices.print', $invoice) }}">
                            <i class="fas fa-print me-2"></i> Print Invoice
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('invoices.download', $invoice) }}">
                            <i class="fas fa-download me-2"></i> Download PDF
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    @if($invoice->status === 'draft')
                        <li>
                            <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-paper-plane me-2"></i> Send to Customer
                                </button>
                            </form>
                        </li>
                    @endif
                    
                    @if($invoice->status === 'sent' && $invoice->payment_status === 'pending')
                        <li>
                            <form action="{{ route('invoices.mark-as-paid', $invoice) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-check-circle me-2"></i> Mark as Paid
                                </button>
                            </form>
                        </li>
                    @endif
                    
                    @if($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
                        <li>
                            <button type="button" class="dropdown-item text-danger" 
                                    data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times-circle me-2"></i> Cancel Invoice
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <!-- Invoice Details -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-receipt me-2"></i>Invoice Details
                </h6>
                <div>
                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 
                                          ($invoice->status === 'sent' ? 'info' : 
                                          ($invoice->status === 'overdue' ? 'danger' : 
                                          ($invoice->status === 'draft' ? 'secondary' : 'warning'))) }} fs-6">
                        {{ ucfirst($invoice->status) }}
                    </span>
                    <span class="badge bg-{{ $invoice->payment_status === 'paid' ? 'success' : 
                                          ($invoice->payment_status === 'partial' ? 'warning' : 'secondary') }} fs-6 ms-2">
                        {{ ucfirst($invoice->payment_status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Customer Information</h6>
                        <div class="mb-3">
                            <strong>{{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}</strong><br>
                            {{ $invoice->customer->email }}<br>
                            {{ $invoice->customer->phone }}
                        </div>
                        
                        @if($invoice->vehicle)
                            <h6 class="text-muted mb-3">Vehicle Information</h6>
                            <div class="mb-3">
                                <strong>{{ $invoice->vehicle->year }} {{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }}</strong><br>
                                VIN: {{ $invoice->vehicle->vin ?? 'N/A' }}<br>
                                License Plate: {{ $invoice->vehicle->license_plate ?? 'N/A' }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Invoice Information</h6>
                        <div class="mb-3">
                            <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                            <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}<br>
                            <strong>Due Date:</strong> 
                            @if($invoice->due_date)
                                {{ $invoice->due_date->format('M d, Y') }}
                                @if($invoice->is_overdue)
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @endif
                            @else
                                No due date
                            @endif
                        </div>
                        
                        @if($invoice->workOrder)
                            <h6 class="text-muted mb-3">Work Order</h6>
                            <div class="mb-3">
                                <strong>WO-{{ str_pad($invoice->workOrder->id, 6, '0', STR_PAD_LEFT) }}</strong><br>
                                {{ $invoice->workOrder->service_type }}<br>
                                Status: {{ ucfirst($invoice->workOrder->status) }}
                            </div>
                        @endif
                    </div>
                </div>
                
                @if($invoice->notes)
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Notes</h6>
                        <div class="alert alert-light">
                            {{ $invoice->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Invoice Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>Invoice Items
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="10%">Type</th>
                                <th width="35%">Item</th>
                                <th width="25%">Description</th>
                                <th width="10%" class="text-end">Qty</th>
                                <th width="10%" class="text-end">Unit Price</th>
                                <th width="10%" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $item->item_type === 'service' ? 'primary' : 
                                                              ($item->item_type === 'parts' ? 'success' : 
                                                              ($item->item_type === 'labor' ? 'warning' : 'info')) }}">
                                            {{ ucfirst($item->item_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->description ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">{{ $invoice->formatted_currency($item->unit_price) }}</td>
                                    <td class="text-end">{{ $invoice->formatted_currency($item->total_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"></td>
                                <td class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">{{ $invoice->formatted_subtotal }}</td>
                            </tr>
                            @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end"><strong>Tax:</strong></td>
                                    <td class="text-end">{{ $invoice->formatted_tax_amount }}</td>
                                </tr>
                            @endif
                            @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end"><strong>Discount:</strong></td>
                                    <td class="text-end">-{{ $invoice->formatted_discount_amount }}</td>
                                </tr>
                            @endif
                            <tr class="table-active">
                                <td colspan="4"></td>
                                <td class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>{{ $invoice->formatted_total }}</strong></td>
                            </tr>
                            @if($invoice->amount_paid > 0)
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end"><strong>Amount Paid:</strong></td>
                                    <td class="text-end text-success">{{ $invoice->formatted_amount_paid }}</td>
                                </tr>
                            @endif
                            @if($invoice->balance_due > 0)
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end"><strong>Balance Due:</strong></td>
                                    <td class="text-end text-danger">{{ $invoice->formatted_balance_due }}</td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Payments -->
        @if($invoice->payments->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave me-2"></i>Payments
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Payment #</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                    <th>Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td>
                                            <a href="{{ route('payments.show', $payment) }}">
                                                {{ $payment->payment_number }}
                                            </a>
                                        </td>
                                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td>{{ $payment->payment_method_name }}</td>
                                        <td class="text-end">{{ $invoice->formatted_currency($payment->amount) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 
                                                                  ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->receiver->name ?? 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <!-- Payment Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-2"></i>Payment Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Invoice Total:</span>
                        <strong>{{ $invoice->formatted_total }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Amount Paid:</span>
                        <strong class="text-success">{{ $invoice->formatted_amount_paid }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Balance Due:</span>
                        <strong class="{{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $invoice->formatted_balance_due }}
                        </strong>
                    </div>
                    
                    @if($invoice->balance_due > 0)
                        <div class="progress mb-3" style="height: 20px;">
                            @php
                                $percentage = ($invoice->amount_paid / $invoice->total_amount) * 100;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $percentage }}%" 
                                 aria-valuenow="{{ $percentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($percentage, 1) }}%
                            </div>
                        </div>
                    @endif
                </div>
                
                @if($invoice->balance_due > 0 && $invoice->status !== 'cancelled')
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" 
                       class="btn btn-success w-100">
                        <i class="fas fa-money-bill-wave me-1"></i> Record Payment
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>Invoice Timeline
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Invoice Created</h6>
                            <p class="text-muted mb-0">
                                {{ $invoice->created_at->format('M d, Y g:i A') }}<br>
                                By: {{ $invoice->creator->name ?? 'System' }}
                            </p>
                        </div>
                    </div>
                    
                    @if($invoice->sent_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Invoice Sent</h6>
                                <p class="text-muted mb-0">
                                    {{ $invoice->sent_at->format('M d, Y g:i A') }}<br>
                                    Method: {{ ucfirst($invoice->delivery_method ?? 'email') }}
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->paid_date)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Invoice Paid</h6>
                                <p class="text-muted mb-0">
                                    {{ $invoice->paid_date->format('M d, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->updated_at && $invoice->updated_at != $invoice->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Updated</h6>
                                <p class="text-muted mb-0">
                                    {{ $invoice->updated_at->format('M d, Y g:i A') }}<br>
