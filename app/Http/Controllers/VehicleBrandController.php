<?php

namespace App\Http\Controllers;

use App\Models\VehicleBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VehicleBrandController extends Controller
{

    public function index()
    {
        $brands = VehicleBrand::withCount('models')
            ->orderBy('name')
            ->get();
        return view('vehicle-brands.index', compact('brands'));
    }

    public function create()
    {
        return view('vehicle-brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_brands,name',
            'description' => 'nullable|string|max:500',
            'country_of_origin' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        VehicleBrand::create($validated);

        return redirect()->route('vehicle-brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function show(VehicleBrand $vehicleBrand)
    {
        $vehicleBrand->load('models');
        return view('vehicle-brands.show', compact('vehicleBrand'));
    }

    public function edit(VehicleBrand $vehicleBrand)
    {
        return view('vehicle-brands.edit', compact('vehicleBrand'));
    }

    public function update(Request $request, VehicleBrand $vehicleBrand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_brands,name,' . $vehicleBrand->id,
            'description' => 'nullable|string|max:500',
            'country_of_origin' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $vehicleBrand->update($validated);

        return redirect()->route('vehicle-brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(VehicleBrand $vehicleBrand)
    {
        if ($vehicleBrand->models()->count() > 0) {
            return redirect()->route('vehicle-brands.index')
                ->with('error', 'Cannot delete brand with existing models. Delete the models first.');
        }

        $vehicleBrand->delete();

        return redirect()->route('vehicle-brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
