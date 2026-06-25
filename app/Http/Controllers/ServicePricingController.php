<?php

namespace App\Http\Controllers;

use App\Models\ServicePricing;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServicePricingController extends Controller
{
    public function index()
    {
        $pricings = ServicePricing::with(['serviceType'])
            ->ordered()
            ->get()
            ->groupBy(fn($p) => $p->serviceType?->name ?? 'Uncategorized');

        $serviceTypes = ServiceType::where('is_active', true)->get();
        $vehicleTypes = ['car', 'suv', 'truck', 'van'];

        return view('service-pricings.index', compact('pricings', 'serviceTypes', 'vehicleTypes'));
    }

    public function create()
    {
        $serviceTypes = ServiceType::where('is_active', true)->get();
        $vehicleTypes = ['car' => 'Car', 'suv' => 'SUV', 'truck' => 'Truck', 'van' => 'Van'];

        return view('service-pricings.create', compact('serviceTypes', 'vehicleTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
            'variant_label' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ServicePricing::create($validated);

        return redirect()->route('service-pricings.index')
            ->with('success', 'Service pricing created successfully.');
    }

    public function edit(ServicePricing $servicePricing)
    {
        $serviceTypes = ServiceType::where('is_active', true)->get();
        $vehicleTypes = ['car' => 'Car', 'suv' => 'SUV', 'truck' => 'Truck', 'van' => 'Van'];

        return view('service-pricings.edit', compact('servicePricing', 'serviceTypes', 'vehicleTypes'));
    }

    public function update(Request $request, ServicePricing $servicePricing)
    {
        $validated = $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
            'variant_label' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $servicePricing->update($validated);

        return redirect()->route('service-pricings.index')
            ->with('success', 'Service pricing updated successfully.');
    }

    public function destroy(ServicePricing $servicePricing)
    {
        $servicePricing->delete();

        return redirect()->route('service-pricings.index')
            ->with('success', 'Service pricing deleted successfully.');
    }

    /**
     * Quick inline update for price/active fields (AJAX-friendly)
     */
    public function quickUpdate(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:service_pricings,id',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $pricing = ServicePricing::findOrFail($validated['id']);
        $pricing->update($validated);

        return response()->json(['success' => true, 'pricing' => $pricing]);
    }
}
