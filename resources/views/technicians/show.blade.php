@extends('layouts.app')

@section('title', 'Technician Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog"></i> Technician Details
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('technicians.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
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

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-id-card"></i> Basic Information
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="avatar-circle mb-3" style="width: 100px; height: 100px; background-color: #3498db; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                            <span style="font-size: 40px; color: white; font-weight: bold;">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <h4>{{ $user->name }}</h4>
                                        <p class="text-muted">{{ $user->specialization ?? 'General Technician' }}</p>
                                    </div>

                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">ID:</th>
                                            <td>{{ $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td>{{ $user->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Experience:</th>
                                            <td>
                                                @if($user->years_experience)
                                                    {{ $user->years_experience }} years
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Shift:</th>
                                            <td>{{ $user->shift_schedule ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($user->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header bg-info text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-phone-alt"></i> Emergency Contact
                                    </h4>
                                </div>
                                <div class="card-body">
                                    @if($user->emergency_contact_name)
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="40%">Name:</th>
                                                <td>{{ $user->emergency_contact_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone:</th>
                                                <td>{{ $user->emergency_contact_phone ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted text-center mb-0">No emergency contact information</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-tools"></i> Skills & Certifications
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Skills</h5>
                                            @if($user->skills && count($user->skills) > 0)
                                                <div class="skills-list">
                                                    @foreach($user->skills as $skill)
                                                        <span class="badge bg-primary mb-1 mr-1">{{ $skill }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted">No skills listed</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Certifications</h5>
                                            @if($user->certifications && count($user->certifications) > 0)
                                                <div class="certifications-list">
                                                    @foreach($user->certifications as $certification)
                                                        <span class="badge bg-warning mb-1 mr-1">{{ $certification }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted">No certifications listed</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header bg-warning text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-history"></i> Recent Activity
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-wrench"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Work Orders</span>
                                                    <span class="info-box-number">
                                                        {{ $user->technicianServiceRecords()->count() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Completed</span>
                                                    <span class="info-box-number">
                                                        {{ $user->technicianServiceRecords()->where('status', 'completed')->count() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Avg. Time</span>
                                                    <span class="info-box-number">
                                                        @php
                                                            $avgTime = $user->technicianServiceRecords()
                                                                ->whereNotNull('completed_at')
                                                                ->whereNotNull('started_at')
                                                                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_minutes')
                                                                ->first();
                                                        @endphp
                                                        @if($avgTime && $avgTime->avg_minutes)
                                                            {{ round($avgTime->avg_minutes / 60, 1) }} hours
                                                        @else
                                                            N/A
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Re-work Rate</span>
                                                    <span class="info-box-number">
                                                        @php
                                                            $totalWorkOrders = $user->technicianServiceRecords()->count();
                                                            $reworkWorkOrders = $user->technicianServiceRecords()
                                                                ->where('requires_rework', true)
                                                                ->count();
                                                            $reworkRate = $totalWorkOrders > 0 ? round(($reworkWorkOrders / $totalWorkOrders) * 100, 1) : 0;
                                                        @endphp
                                                        {{ $reworkRate }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header bg-secondary text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-calendar-alt"></i> Recent Work Orders
                                    </h4>
                                </div>
                                <div class="card-body">
                                    @php
                                        $recentWorkOrders = $user->technicianServiceRecords()
                                            ->with(['vehicle', 'customer'])
                                            ->orderBy('created_at', 'desc')
                                            ->limit(5)
                                            ->get();
                                    @endphp

                                    @if($recentWorkOrders->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Work Order #</th>
                                                        <th>Vehicle</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentWorkOrders as $workOrder)
                                                        <tr>
                                                            <td>#{{ $workOrder->id }}</td>
                                                            <td>
                                                                @if($workOrder->vehicle)
                                                                    {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge 
                                                                    @if($workOrder->status == 'completed') bg-success
                                                                    @elseif($workOrder->status == 'in_progress') bg-warning
                                                                    @elseif($workOrder->status == 'pending') bg-info
                                                                    @else bg-secondary @endif">
                                                                    {{ ucfirst($workOrder->status) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $workOrder->created_at->format('M d, Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center mb-0">No recent work orders</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group">
                        <a href="{{ route('technicians.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Technician
                        </a>
                        <form action="{{ route('technicians.destroy', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this technician?')">
                                <i class="fas fa-trash"></i> Delete Technician
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection