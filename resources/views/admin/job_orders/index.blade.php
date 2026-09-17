@extends('layouts.app')

@section('title', 'Job Orders (Admin)')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Job Orders</h1>

    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <div class="flex space-x-2">
            <a href="{{ route('admin.job-orders.index') }}" class="px-3 py-2 bg-gray-200 rounded">All</a>
            <a href="{{ route('admin.job-orders.index', ['status' => 'draft']) }}" class="px-3 py-2 bg-gray-200 rounded">Draft</a>
            <a href="{{ route('admin.job-orders.index', ['status' => 'in_progress']) }}" class="px-3 py-2 bg-gray-200 rounded">In Progress</a>
            <a href="{{ route('admin.job-orders.index', ['status' => 'completed']) }}" class="px-3 py-2 bg-gray-200 rounded">Completed</a>
        </div>
        <a href="{{ route('admin.job-orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ New Job Order</a>
    </div>

    <form method="GET" class="mb-4 flex items-center gap-2">
        <input type="text" name="customer_id" placeholder="Customer ID" value="{{ request('customer_id') }}" class="border rounded px-2 py-1 text-sm">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1 text-sm">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1 text-sm">
        <button type="submit" class="px-3 py-1 bg-gray-700 text-white rounded text-sm">Filter</button>
    </form>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($jobOrders as $jobOrder)
                    <tr>
                        <td class="px-4 py-2 text-sm font-medium">{{ $jobOrder->job_order_number }}</td>
                        <td class="px-4 py-2 text-sm">
                            {{ optional($jobOrder->customer)->first_name }} {{ optional($jobOrder->customer)->last_name }}
                        </td>
                        <td class="px-4 py-2 text-sm">
                            @if ($jobOrder->vehicle)
                                {{ $jobOrder->vehicle->year ?? '' }} {{ $jobOrder->vehicle->make }} {{ $jobOrder->vehicle->model }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $jobOrder->job_order_status }}</td>
                        <td class="px-4 py-2 text-sm">{{ $jobOrder->job_order_date }}</td>
                        <td class="px-4 py-2 text-sm">
                            <a href="{{ route('admin.job-orders.show', $jobOrder) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No job orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $jobOrders->links() }}</div>
</div>
@endsection
