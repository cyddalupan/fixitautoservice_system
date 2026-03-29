@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary"></i> Quotations
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Search quotations..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Quotation
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>License Plate</th>
                                    <th>Preferred Date</th>
                                    <th>Service Type</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotations as $quotation)
                                    <tr>
                                        <td>#{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $quotation->name }}</div>
                                            <small class="text-muted">{{ $quotation->email }}</small><br>
                                            <small class="text-muted">{{ $quotation->phone }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $quotation->vehicle_make }} {{ $quotation->vehicle_model }}</div>
                                            <small class="text-muted">Year: {{ $quotation->vehicle_year }}</small>
                                        </td>
                                        <td>
                                            @if($quotation->license_plate)
                                                <span class="badge bg-dark">{{ $quotation->license_plate }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($quotation->preferred_date)
                                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($quotation->preferred_date)->format('M d, Y') }}</div>
                                                @if($quotation->preferred_time)
                                                    <small class="text-muted">{{ $quotation->preferred_time }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                {{ $quotation->service_type }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'reviewed' => 'info',
                                                    'contacted' => 'primary',
                                                    'converted' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                
                                                $statusLabels = [
                                                    'pending' => 'Pending',
                                                    'reviewed' => 'Reviewed',
                                                    'contacted' => 'Contacted',
                                                    'converted' => 'Converted',
                                                    'rejected' => 'Rejected'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$quotation->status] }}">
                                                {{ $statusLabels[$quotation->status] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $quotation->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $quotation->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('quotations.destroy', $quotation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quotation?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <h5>No quotations found</h5>
                                                <p>When customers submit quote requests from the website, they will appear here.</p>
                                                <a href="{{ route('quotations.create') }}" class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus"></i> Create First Quotation
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($quotations->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $quotations->links() }}
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Total Quotations</h6>
                                        <h3 class="text-primary">{{ $quotations->total() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Pending</h6>
                                        <h3 class="text-warning">{{ $quotations->where('status', 'pending')->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Converted</h6>
                                        <h3 class="text-success">{{ $quotations->where('status', 'converted')->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Contacted</h6>
                                        <h3 class="text-info">{{ $quotations->where('status', 'contacted')->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchButton').addEventListener('click', function() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });
</script>
@endsection