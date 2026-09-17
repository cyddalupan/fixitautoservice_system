<?php

namespace App\Http\Controllers;

use App\Models\ServicePricing;
use App\Models\ServiceType;
use App\Models\VehicleBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicePricingController extends Controller
{
    public function index(Request $request)
    {
        $query = ServicePricing::with(['serviceType']);

        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('brand')) {
            $query->where('brand_name', $request->brand);
        }

        if ($request->filled('model')) {
            $query->where('model_name', $request->model);
        }

        $pricings = $query->ordered()
            ->get()
            ->groupBy(fn($p) => $p->serviceType?->name ?? 'Uncategorized');

        $serviceTypes = ServiceType::where('is_active', true)->get();
        $vehicleTypes = ['Automatic', 'Manual'];
        $brands = VehicleBrand::orderBy('name')->get();
        $models = DB::table('service_pricings')
            ->whereNotNull('model_name')
            ->select('brand_name', 'model_name')
            ->distinct()
            ->orderBy('brand_name')
            ->orderBy('model_name')
            ->get()
            ->groupBy('brand_name');

        return view('service-pricings.index', compact('pricings', 'serviceTypes', 'vehicleTypes', 'brands', 'models'));
    }

    public function create()
    {
        $serviceTypes = ServiceType::where('is_active', true)->get();
        $vehicleTypes = ['Automatic' => 'Automatic', 'Manual' => 'Manual'];

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
        $vehicleTypes = ['Automatic' => 'Automatic', 'Manual' => 'Manual'];

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
