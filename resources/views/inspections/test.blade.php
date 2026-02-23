@extends('layouts.app')

@section('title', 'Test Inspections')

@section('content')
<div class="container">
    <h1>Test Vehicle Inspections</h1>
    
    @php
        try {
            $inspections = \App\Models\VehicleInspection::with(['customer', 'vehicle'])->limit(5)->get();
            echo '<p>Found ' . $inspections->count() . ' inspections</p>';
            
            foreach($inspections as $inspection) {
                echo '<div class="card mb-3">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">Inspection #' . $inspection->id . '</h5>';
                echo '<p class="card-text">Customer: ' . ($inspection->customer ? $inspection->customer->first_name : 'No customer') . '</p>';
                echo '<p class="card-text">Vehicle: ' . ($inspection->vehicle ? $inspection->vehicle->make . ' ' . $inspection->vehicle->model : 'No vehicle') . '</p>';
                echo '</div>';
                echo '</div>';
            }
        } catch (\Exception $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    @endphp
</div>
@endsection