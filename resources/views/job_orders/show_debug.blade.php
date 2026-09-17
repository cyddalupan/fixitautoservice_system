@extends('layouts.app')

@section('title', 'Work Order ' . $jobOrder->job_order_number)

@section('content')
<div class="container">
    <h1>Work Order: {{ $jobOrder->job_order_number }}</h1>
    
    <h2>Debug Information:</h2>
    <pre>
Work Order ID: {{ $jobOrder->id }}
Work Order Number: {{ $jobOrder->job_order_number }}
Customer: {{ $jobOrder->customer->first_name ?? 'NULL' }} {{ $jobOrder->customer->last_name ?? 'NULL' }}
Vehicle: {{ $jobOrder->vehicle->make ?? 'NULL' }} {{ $jobOrder->vehicle->model ?? 'NULL' }}
Timeline variable exists: {{ isset($timeline) ? 'YES' : 'NO' }}
Timeline count: {{ isset($timeline) ? count($timeline) : 0 }}
Technicians count: {{ isset($technicians) ? $technicians->count() : 0 }}
Customer Work Orders count: {{ isset($customerJobOrders) ? $customerJobOrders->count() : 0 }}
    </pre>
    
    <a href="{{ route('job-orders.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection