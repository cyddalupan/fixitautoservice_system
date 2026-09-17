@extends('layouts.app')

@section('title', 'Edit Job Order ' . $jobOrder->job_order_number)

@section('content')
<div class="container mx-auto p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Edit Job Order {{ $jobOrder->job_order_number }}</h1>
        <a href="{{ route('admin.job-orders.index') }}" class="px-3 py-2 bg-gray-200 rounded text-sm">← Back</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.job-orders.update', $jobOrder) }}" class="bg-white rounded shadow p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Customer</label>
                <select name="customer_id" required class="w-full border rounded px-3 py-2">
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected($jobOrder->customer_id === $customer->id)>
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-1">Vehicle</label>
                <select name="vehicle_id" class="w-full border rounded px-3 py-2">
                    <option value="">— None —</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected($jobOrder->vehicle_id === $vehicle->id)>
                            {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year ?? '—' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-1">Status</label>
                <select name="job_order_status" class="w-full border rounded px-3 py-2">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($jobOrder->job_order_status === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Blueprint lifecycle: pending → in_progress → completed.</p>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-1">Technician</label>
                <select name="technician_id" class="w-full border rounded px-3 py-2">
                    <option value="">— Unassigned —</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}" @selected($jobOrder->technician_id === $technician->id)>
                            {{ $technician->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <label class="block font-semibold text-gray-700 mb-1">Notes</label>
            <textarea name="internal_notes" rows="4" class="w-full border rounded px-3 py-2">{{ old('internal_notes', $jobOrder->internal_notes) }}</textarea>
        </div>

        <div class="mt-4">
            <label class="block font-semibold text-gray-700 mb-1">Services (from catalog)</label>
            <div id="service-rows" class="space-y-2">
                @foreach ($jobOrder->items as $item)
                    <div class="flex items-center gap-2">
                        <select name="items[][catalog_id]" class="border rounded px-3 py-2 flex-1" onchange="applyDefaultPrice(this)">
                            @foreach ($services as $svc)
                                <option value="{{ $svc->id }}" data-price="{{ (float) $svc->default_price }}" @selected($item->description === $svc->name)>
                                    {{ $svc->name }} (₱{{ number_format($svc->default_price, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="items[][quantity]" value="{{ $item->quantity }}" min="1" step="1" class="border rounded px-3 py-2 w-20" placeholder="Qty">
                        <input type="number" name="items[][unit_price]" value="{{ $item->unit_cost }}" step="0.01" class="border rounded px-3 py-2 w-32" placeholder="Price">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-600">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addServiceRow()" class="mt-2 px-3 py-1 bg-gray-200 rounded text-sm">+ Add Service</button>
        </div>

        <script>
            const SERVICE_CATALOG = @json($services->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->default_price])->values());

            function addServiceRow() {
                const rows = document.getElementById('service-rows');
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2';
                let opts = '<option value="">Select service</option>';
                SERVICE_CATALOG.forEach(s => { opts += `<option value="${s.id}" data-price="${s.price}">${s.name} (₱${s.price.toFixed(2)})</option>`; });
                row.innerHTML = `
                    <select name="items[][catalog_id]" class="border rounded px-3 py-2 flex-1" onchange="applyDefaultPrice(this)">${opts}</select>
                    <input type="number" name="items[][quantity]" value="1" min="1" step="1" class="border rounded px-3 py-2 w-20" placeholder="Qty">
                    <input type="number" name="items[][unit_price]" step="0.01" class="border rounded px-3 py-2 w-32" placeholder="Price (auto)">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600">✕</button>
                `;
                rows.appendChild(row);
            }

            function applyDefaultPrice(sel) {
                const price = sel.selectedOptions[0]?.dataset.price ?? '';
                sel.closest('div').querySelector('input[name="items[][unit_price]"]').value = price;
            }
        </script>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Changes</button>
            <a href="{{ route('admin.job-orders.show', $jobOrder) }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
        </div>
    </form>
</div>
@endsection
