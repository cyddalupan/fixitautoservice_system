@extends('layouts.app')

@section('title', 'View Estimate - ' . $estimate->estimate_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Estimate #{{ $estimate->estimate_number }}</h2>
                <div>
                    <a href="{{ route('estimates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if($estimate->status !== 'accepted' && $estimate->status !== 'rejected')
                    <a href="{{ route('estimates.edit', $estimate->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                    <button type="button" class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer & Vehicle Info -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Customer Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Name:</th>
                            <td>{{ $estimate->customer->first_name }} {{ $estimate->customer->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $estimate->customer->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $estimate->customer->phone }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $estimate->customer->address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-car"></i> Vehicle Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Make:</th>
                            <td>{{ $estimate->vehicle->make }}</td>
                        </tr>
                        <tr>
                            <th>Model:</th>
                            <td>{{ $estimate->vehicle->model }}</td>
                        </tr>
                        <tr>
                            <th>Year:</th>
                            <td>{{ $estimate->vehicle->year }}</td>
                        </tr>
                        <tr>
                            <th>VIN:</th>
                            <td>{{ $estimate->vehicle->vin ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>License Plate:</th>
                            <td>{{ $estimate->vehicle->license_plate }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Estimate Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Estimate Date:</strong> {{ \Carbon\Carbon::parse($estimate->estimate_date)->format('M d, Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($estimate->valid_until)->format('M d, Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong> 
                            @php
                                $statusClass = match($estimate->status) {
                                    'draft' => 'secondary',
                                    'pending' => 'info',
                                    'viewed' => 'warning',
                                    'accepted' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($estimate->status) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Technician:</strong> {{ $estimate->technician->name ?? 'Not assigned' }}
                        </div>
                    </div>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item / Service</th>
                                <th>Category</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($estimate->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $item->inventory->name ?? 'N/A' }}
                                    @if($item->description)
                                    <br><small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->inventory->category->name ?? 'Service' }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">₱{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Subtotal:</th>
                                <th class="text-end">₱{{ number_format($estimate->subtotal, 2) }}</th>
                            </tr>
                            @if($estimate->tax_rate > 0)
                            <tr>
                                <th colspan="5" class="text-end">Tax ({{ $estimate->tax_rate }}%):</th>
                                <th class="text-end">₱{{ number_format($estimate->tax_amount, 2) }}</th>
                            </tr>
                            @endif
                            @if($estimate->discount_amount > 0)
                            <tr>
                                <th colspan="5" class="text-end">Discount:</th>
                                <th class="text-end">-₱{{ number_format($estimate->discount_amount, 2) }}</th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="5" class="text-end">Total:</th>
                                <th class="text-end">₱{{ number_format($estimate->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    @if($estimate->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Notes:</strong>
                            <p>{{ $estimate->notes }}</p>
                        </div>
                    </div>
                    @endif

                    @if($estimate->terms_conditions)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Terms & Conditions:</strong>
                            <p>{{ $estimate->terms_conditions }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($estimate->status === 'pending' || $estimate->status === 'viewed')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('estimates.update-status', $estimate->id) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Accept Estimate
                        </button>
                    </form>
                    <form method="POST" action="{{ route('estimates.update-status', $estimate->id) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject Estimate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
