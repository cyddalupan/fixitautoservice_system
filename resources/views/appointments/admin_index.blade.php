@extends('layouts.app')

@section('title', 'Appointments (Admin)')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Appointments</h1>

    @if (session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex space-x-2">
        <a href="{{ route('admin.appointments.index') }}" class="px-3 py-2 bg-gray-200 rounded">All</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'scheduled']) }}" class="px-3 py-2 bg-blue-200 rounded">Scheduled</a>
        <a href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}" class="px-3 py-2 bg-red-200 rounded">Cancelled</a>
        <a href="{{ route('admin.appointments.cancelled') }}" class="px-3 py-2 bg-gray-300 rounded">Cancelled (list)</a>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $appointment->appointment_number ?? $appointment->id }}</td>
                        <td class="px-4 py-2 text-sm">{{ $appointment->customer?->first_name }} {{ $appointment->customer?->last_name }}</td>
                        <td class="px-4 py-2 text-sm">{{ $appointment->appointment_date }}</td>
                        <td class="px-4 py-2 text-sm">{{ $appointment->appointment_time }}</td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs
                                {{ $appointment->appointment_status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $appointment->appointment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $appointment->booking_source }}</td>
                        <td class="px-4 py-2 text-sm">
                            <form method="POST" action="{{ route('admin.appointments.destroy', $appointment->id) }}" onsubmit="return confirm('Delete this appointment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
