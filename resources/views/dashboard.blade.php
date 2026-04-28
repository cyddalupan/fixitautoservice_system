@extends('layouts.app')

@section('title', 'Dashboard - Fix-It Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty Dashboard State -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-chart-line fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">Dashboard is Empty</h3>
                        <p class="text-muted mb-4">
                            The dashboard has been cleared. You can now add your own widgets, charts, or content.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('analytics') }}" class="btn btn-primary me-2">
                                <i class="fas fa-chart-bar me-1"></i> Go to Analytics
                            </a>
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-calendar me-1"></i> View Appointments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection