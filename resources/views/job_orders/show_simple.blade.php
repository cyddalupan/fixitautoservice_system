@extends('layouts.app')

@section('title', 'Work Order ' . $jobOrder->job_order_number)

@section('content')
<div class="container">
    <h1>Work Order: {{ $jobOrder->job_order_number }}</h1>
    <p>Status: {{ $jobOrder->job_order_status }}</p>
    <p>Customer: {{ $jobOrder->customer->first_name ?? 'N/A' }} {{ $jobOrder->customer->last_name ?? '' }}</p>
    <p>Vehicle: {{ $jobOrder->vehicle->make ?? 'N/A' }} {{ $jobOrder->vehicle->model ?? '' }}</p>
    <p>Created: {{ $jobOrder->created_at->format('Y-m-d') }}</p>
    
    <a href="{{ route('job-orders.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection