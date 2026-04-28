@extends('layouts.app')

@section('title', 'Vehicle Colors')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-palette me-2"></i>Vehicle Colors</h1>
            <p class="text-muted mb-0">Manage all vehicle colors in the system</p>
        </div>
        <a href="{{ route('vehicle-colors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Color
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Color</th>
                        <th>Swatch</th>
                        <th>Hex Code</th>
                        <th>Popularity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colors as $color)
                    <tr>
                        <td>{{ $color->id }}</td>
                        <td><strong>{{ $color->name }}</strong></td>
                        <td>
                            @if($color->hex_code)
                                <span style="display:inline-block;width:30px;height:30px;border-radius:50%;background:{{ $color->hex_code }};border:1px solid #ccc;vertical-align:middle;"></span>
                            @else
                                —
                            @endif
                        </td>
                        <td><code>{{ $color->hex_code ?? '—' }}</code></td>
                        <td>{{ $color->popularity_score }}</td>
                        <td>
                            @if($color->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('vehicle-colors.edit', $color) }}" class="btn btn-sm btn-warning"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('vehicle-colors.destroy', $color) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete color {{ $color->name }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No colors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
