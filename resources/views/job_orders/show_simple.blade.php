@extends('layouts.app')

@section('title', 'Work Order ' . $workOrder->work_order_number)

@section('content')
<div class="container">
    <h1>Work Order: {{ $workOrder->work_order_number }}</h1>
    <p>Status: {{ $workOrder->work_order_status }}</p>
    <p>Customer: {{ $workOrder->customer->first_name ?? 'N/A' }} {{ $workOrder->customer->last_name ?? '' }}</p>
    <p>Vehicle: {{ $workOrder->vehicle->make ?? 'N/A' }} {{ $workOrder->vehicle->model ?? '' }}</p>
    <p>Created: {{ $workOrder->created_at->format('Y-m-d') }}</p>
    
    <a href="{{ route('work-orders.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection