@extends('layouts.app')

@section('title', 'Technician Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog"></i> Technician Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('technicians.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Add New Technician
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Specialization</th>
                                    <th>Experience</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($technicians as $technician)
                                    <tr>
                                        <td>{{ $technician->id }}</td>
                                        <td>
                                            <strong>{{ $technician->name }}</strong>
                                            @if($technician->skills)
                                                <br>
                                                <small class="text-muted">
                                                    Skills: {{ implode(', ', array_slice($technician->skills, 0, 3)) }}
                                                    @if(count($technician->skills) > 3)
                                                        ...
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $technician->email }}</td>
                                        <td>{{ $technician->phone ?? 'N/A' }}</td>
                                        <td>{{ $technician->specialization ?? 'General' }}</td>
                                        <td>
                                            @if($technician->years_experience)
                                                {{ $technician->years_experience }} years
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($technician->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('technicians.show', $technician) }}" class="btn btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('technicians.edit', $technician) }}" class="btn btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('technicians.destroy', $technician) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this technician?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                                <h5>No technicians found</h5>
                                                <p class="text-muted">Add your first technician to get started.</p>
                                                <a href="{{ route('technicians.create') }}" class="btn btn-success">
                                                    <i class="fas fa-plus"></i> Add Technician
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $technicians->links() }}
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Total Technicians:</strong> {{ $technicians->total() }}
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Showing {{ $technicians->firstItem() }} to {{ $technicians->lastItem() }} of {{ $technicians->total() }} entries
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });
</script>
@endsection