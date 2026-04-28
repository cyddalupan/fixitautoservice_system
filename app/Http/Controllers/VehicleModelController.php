<?php

namespace App\Http\Controllers;

use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{

    public function index(Request $request)
    {
        $query = VehicleModel::with('brand');
        
        if ($request->filled('brand_id')) {
            $query->where('vehicle_brand_id', $request->brand_id);
        }
        
        $models = $query->orderBy('name')->get();
        $brands = VehicleBrand::orderBy('name')->get();
        
        return view('vehicle-models.index', compact('models', 'brands'));
    }

    public function create()
    {
        $brands = VehicleBrand::orderBy('name')->get();
        return view('vehicle-models.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'name' => 'required|string|max:50',
            'year_start' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'year_end' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'body_type' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        VehicleModel::create($validated);

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Model created successfully.');
    }

    public function show(VehicleModel $vehicleModel)
    {
        $vehicleModel->load('brand');
        return view('vehicle-models.show', compact('vehicleModel'));
    }

    public function edit(VehicleModel $vehicleModel)
    {
        $brands = VehicleBrand::orderBy('name')->get();
        return view('vehicle-models.edit', compact('vehicleModel', 'brands'));
    }

    public function update(Request $request, VehicleModel $vehicleModel)
    {
        $validated = $request->validate([
            'vehicle_brand_id' => 'required|exists:vehicle_brands,id',
            'name' => 'required|string|max:50',
            'year_start' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'year_end' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'body_type' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $vehicleModel->update($validated);

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Model updated successfully.');
    }

    public function destroy(VehicleModel $vehicleModel)
    {
        $vehicleModel->delete();

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Model deleted successfully.');
    }
}
