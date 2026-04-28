@extends('layouts.app')

@section('title', 'Work Order ' . $workOrder->work_order_number)

@section('content')
<div class="container">
    <h1>Work Order: {{ $workOrder->work_order_number }}</h1>
    
    <h2>Debug Information:</h2>
    <pre>
Work Order ID: {{ $workOrder->id }}
Work Order Number: {{ $workOrder->work_order_number }}
Customer: {{ $workOrder->customer->first_name ?? 'NULL' }} {{ $workOrder->customer->last_name ?? 'NULL' }}
Vehicle: {{ $workOrder->vehicle->make ?? 'NULL' }} {{ $workOrder->vehicle->model ?? 'NULL' }}
Timeline variable exists: {{ isset($timeline) ? 'YES' : 'NO' }}
Timeline count: {{ isset($timeline) ? count($timeline) : 0 }}
Technicians count: {{ isset($technicians) ? $technicians->count() : 0 }}
Customer Work Orders count: {{ isset($customerWorkOrders) ? $customerWorkOrders->count() : 0 }}
    </pre>
    
    <a href="{{ route('work-orders.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection