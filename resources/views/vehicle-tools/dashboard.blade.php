@extends('layouts.app')

@section('title', 'Vehicle Tools Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Vehicle Tools Dashboard</h1>
            <p class="text-muted">Manage VIN decoding, recall notifications, and service history tools</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('vehicle-tools.vin-decoder') }}" class="btn btn-primary">
                    <i class="fas fa-barcode"></i> VIN Decoder
                </a>
                <a href="{{ route('recalls.index') }}" class="btn btn-warning">
                    <i class="fas fa-exclamation-triangle"></i> Recalls
                </a>
                <a href="{{ route('vehicle-tools.service-history') }}" class="btn btn-info">
                    <i class="fas fa-history"></i> Service History
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Vehicles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalVehicles) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                VINs Decoded</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($vehiclesDecoded) }}</div>
                            <div class="text-xs text-muted mt-1">{{ number_format($vehiclesWithVIN) }} with VIN</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Open Recalls</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($vehiclesWithRecalls) }}</div>
                            <div class="text-xs text-muted mt-1">{{ number_format($vehiclesNeedingRecallCheck) }} need check</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Cache Stats</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($cacheEntries) }}</div>
                            <div class="text-xs text-muted mt-1">{{ number_format($cacheHits) }} hits</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Recalls -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Recent Recalls
                    </h6>
                    <a href="{{ route('recalls.index') }}" class="btn btn-sm btn-warning">View All</a>
                </div>
                <div class="card-body">
                    @if($recentRecalls->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Component</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRecalls as $recall)
                                    <tr>
                                        <td>
                                            <a href="{{ route('recalls.show', $recall->id) }}" class="text-decoration-none">
                                                {{ $recall->vehicle->year }} {{ $recall->vehicle->make }} {{ $recall->vehicle->model }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($recall->component, 20) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $recall->status_color }}">
                                                {{ ucfirst($recall->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $recall->recall_date->format('m/d/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No recent recalls found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Vehicles Needing VIN Decoding -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-barcode"></i> Vehicles Needing VIN Decoding
                    </h6>
                    <a href="{{ route('vehicle-tools.vin-decoder') }}" class="btn btn-sm btn-primary">Decode VINs</a>
                </div>
                <div class="card-body">
                    @if($vehiclesNeedingDecoding->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>VIN</th>
                                        <th>Customer</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehiclesNeedingDecoding as $vehicle)
                                    <tr>
                                        <td>
                                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                        </td>
                                        <td>
                                            <code>{{ $vehicle->vin }}</code>
                                        </td>
                                        <td>
                                            @if($vehicle->customer)
                                                {{ $vehicle->customer->first_name }} {{ $vehicle->customer->last_name }}
                                            @else
                                                <span class="text-muted">No customer</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vehicle-tools.vin-decoder') }}?vin={{ $vehicle->vin }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-magic"></i> Decode
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">All vehicles with VINs have been decoded</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-primary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-barcode fa-2x text-primary mb-3"></i>
                                    <h5 class="card-title">VIN Decoder</h5>
                                    <p class="card-text text-muted small">Decode VINs to get vehicle specifications</p>
                                    <a href="{{ route('vehicle-tools.vin-decoder') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-play"></i> Start Decoding
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-warning h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                                    <h5 class="card-title">Check Recalls</h5>
                                    <p class="card-text text-muted small">Check for vehicle safety recalls</p>
                                    <a href="{{ route('recalls.index') }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-search"></i> Check Now
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-2x text-info mb-3"></i>
                                    <h5 class="card-title">Service History</h5>
                                    <p class="card-text text-muted small">View vehicle service records</p>
                                    <a href="{{ route('vehicle-tools.service-history') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-list"></i> View History
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-left-success h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-export fa-2x text-success mb-3"></i>
                                    <h5 class="card-title">Export Data</h5>
                                    <p class="card-text text-muted small">Export vehicle and recall data</p>
                                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#exportModal">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Management -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-database"></i> VIN Cache Management
                    </h6>
                    @if($expiredCache > 0)
                        <form action="{{ route('vehicle-tools.clear-expired-cache') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i> Clear {{ $expiredCache }} Expired Entries
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="display-4 text-primary">{{ number_format($cacheEntries) }}</div>
                            <p class="text-muted">Cache Entries</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 text-success">{{ number_format($cacheHits) }}</div>
                            <p class="text-muted">Total Hits</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-4 {{ $expiredCache > 0 ? 'text-warning' : 'text-success' }}">
                                {{ number_format($expiredCache) }}
                            </div>
                            <p class="text-muted">Expired Entries</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a href="{{ route('vehicle-tools.export-vehicle-data') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-car text-primary mr-2"></i>
                        Export Vehicle Data (JSON)
                    </a>
                    <a href="{{ route('recalls.export', ['format' => 'json']) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                        Export Recall Data (JSON)
                    </a>
                    <a href="{{ route('recalls.export', ['format' => 'csv']) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                        Export Recall Data (CSV)
                    </a>
                    <a href="{{ route('vin-decoder.export', ['format' => 'json']) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-barcode text-info mr-2"></i>
                        Export VIN Cache (JSON)
                    </a>
                    <a href="{{ route('vin-decoder.export', ['format' => 'csv']) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-barcode text-info mr-2"></i>
                        Export VIN Cache (CSV)
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        window.location.reload();
    }, 300000); // 5 minutes
</script>
@endsection