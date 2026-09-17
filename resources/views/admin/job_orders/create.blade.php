@extends('layouts.app')

@section('title', 'New Job Order')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">New Job Order</h1>

    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.job-orders.store') }}" class="bg-white rounded shadow p-6 max-w-2xl space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Customer</label>
            <select name="customer_id" id="customer-select" class="mt-1 w-full border rounded px-3 py-2" required>
                <option value="">Select customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" data-search="{{ strtolower($customer->first_name) }} {{ strtolower($customer->last_name) }}">{{ $customer->first_name }} {{ $customer->last_name }}</option>
                @endforeach
            </select>
            <input type="text" placeholder="Type to search customer…" oninput="filterCustomers(this.value)" class="mt-2 w-full border rounded px-3 py-1 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Vehicle</label>
            <select name="vehicle_id" id="vehicle-select" class="mt-1 w-full border rounded px-3 py-2">
                <option value="">Select vehicle (optional)</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" data-customer="{{ $vehicle->customer_id }}">
                        {{ $vehicle->year ?? '' }} {{ $vehicle->make }} {{ $vehicle->model }}
                        ({{ optional($vehicle->customer)->first_name }} {{ optional($vehicle->customer)->last_name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Technician</label>
            <select name="technician_id" class="mt-1 w-full border rounded px-3 py-2">
                <option value="">Select technician (optional)</option>
                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}" @selected(old('technician_id') == $technician->id)>{{ $technician->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Job Order Date</label>
            <input type="date" name="job_order_date" value="{{ old('job_order_date', date('Y-m-d')) }}" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="job_order_status" class="mt-1 w-full border rounded px-3 py-2">
                @foreach (['draft', 'pending', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'completed', 'cancelled', 'invoiced'] as $status)
                    <option value="{{ $status }}" @selected(old('job_order_status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Services</label>
            <div id="service-rows" class="space-y-2"></div>
            <button type="button" onclick="addServiceRow()" class="mt-2 px-3 py-1 bg-gray-200 rounded text-sm">+ Add Service</button>
        </div>

        <script>
            const SERVICE_CATALOG = @json($services->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->default_price])->values());

            function filterCustomers(q) {
                q = q.toLowerCase();
                document.querySelectorAll('#customer-select option').forEach(o => {
                    if (o.value === '') return;
                    o.style.display = (o.dataset.search || '').includes(q) ? '' : 'none';
                });
            }

            document.getElementById('customer-select').addEventListener('change', function () {
                const cid = this.value;
                document.querySelectorAll('#vehicle-select option').forEach(o => {
                    if (o.value === '') return;
                    o.style.display = (o.dataset.customer === cid) ? '' : 'none';
                });
            });

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

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.job-orders.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create Job Order</button>
        </div>
    </form>
</div>
@endsection
