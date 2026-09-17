@extends('layouts.app')

@section('title', 'Invoice Statistics')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Invoice Statistics</h1>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Back to Invoices</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Total Invoices</h6>
                    <h2 class="mb-0">{{ number_format($totalInvoices) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="card-title">Total Amount</h6>
                    <h2 class="mb-0">₱{{ number_format($totalAmount, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="card-title">Total Paid</h6>
                    <h2 class="mb-0">₱{{ number_format($totalPaid, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="card-title">Total Due</h6>
                    <h2 class="mb-0">₱{{ number_format($totalDue, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Status Breakdown</div>
                <div class="card-body">
                    @forelse($statusCounts as $status => $count)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ ucfirst($status) }}</span>
                            <strong>{{ $count }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No invoices yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Overdue</div>
                <div class="card-body">
                    <h2 class="mb-0">{{ number_format($overdueInvoices) }}</h2>
                    <p class="text-muted mb-0">Invoices past due or unpaid.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
