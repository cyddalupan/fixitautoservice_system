@extends('layouts.app')

@section('title', 'Job Order ' . $jobOrder->job_order_number)

@section('content')
<div class="container mx-auto p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Job Order {{ $jobOrder->job_order_number }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.job-orders.print', $jobOrder) }}" class="px-3 py-2 bg-green-600 text-white rounded text-sm">Print</a>
            <a href="{{ route('admin.job-orders.print-tech', $jobOrder) }}" class="px-3 py-2 bg-stone-600 text-white rounded text-sm">Print Tech</a>
            <a href="{{ route('admin.job-orders.edit', $jobOrder) }}" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Edit</a>
            <form method="POST" action="{{ route('admin.job-orders.destroy', $jobOrder) }}" onsubmit="return confirm('Delete this job order?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded text-sm">Delete</button>
            </form>
            <a href="{{ route('admin.job-orders.index') }}" class="px-3 py-2 bg-gray-200 rounded text-sm">← Back</a>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h2 class="font-semibold text-gray-700 mb-1">Customer</h2>
            <p>{{ optional($jobOrder->customer)->first_name }} {{ optional($jobOrder->customer)->last_name }}</p>
            <p class="text-sm text-gray-500">{{ optional($jobOrder->customer)->email }}</p>
            <p class="text-sm text-gray-500">{{ optional($jobOrder->customer)->phone }}</p>
        </div>
        <div>
            <h2 class="font-semibold text-gray-700 mb-1">Vehicle</h2>
            @if ($jobOrder->vehicle)
                <p>{{ $jobOrder->vehicle->year ?? '' }} {{ $jobOrder->vehicle->make }} {{ $jobOrder->vehicle->model }}</p>
                <p class="text-sm text-gray-500">Plate: {{ $jobOrder->vehicle->license_plate }}</p>
            @else
                <p class="text-gray-500">—</p>
            @endif

            <h2 class="font-semibold text-gray-700 mt-4 mb-1">Status</h2>
            <p>{{ $jobOrder->job_order_status }}</p>
            <p class="text-sm text-gray-500">Date: {{ $jobOrder->job_order_date }}</p>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <h2 class="font-semibold text-gray-700 mb-3">Services</h2>
        @if ($jobOrder->items && $jobOrder->items->count())
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($jobOrder->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm">{{ $item->description ?? $item->item_type }}</td>
                            <td class="px-4 py-2 text-sm">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-sm">{{ $item->unit_cost }}</td>
                            <td class="px-4 py-2 text-sm">{{ $item->total_cost }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-gray-500">No services added to this job order yet.</p>
        @endif
    </div>
</div>
@endsection
