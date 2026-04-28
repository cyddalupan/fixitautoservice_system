<?php

namespace App\Http\Controllers;

use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VehicleColorController extends Controller
{

    public function index()
    {
        $colors = VehicleColor::orderBy('popularity_score', 'desc')
            ->orderBy('name')
            ->get();
        return view('vehicle-colors.index', compact('colors'));
    }

    public function create()
    {
        return view('vehicle-colors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_colors,name',
            'hex_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        VehicleColor::create($validated);

        return redirect()->route('vehicle-colors.index')
            ->with('success', 'Color created successfully.');
    }

    public function show(VehicleColor $vehicleColor)
    {
        return view('vehicle-colors.edit', compact('vehicleColor'));
    }

    public function edit(VehicleColor $vehicleColor)
    {
        return view('vehicle-colors.edit', compact('vehicleColor'));
    }

    public function update(Request $request, VehicleColor $vehicleColor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_colors,name,' . $vehicleColor->id,
            'hex_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $vehicleColor->update($validated);

        return redirect()->route('vehicle-colors.index')
            ->with('success', 'Color updated successfully.');
    }

    public function destroy(VehicleColor $vehicleColor)
    {
        $vehicleColor->delete();

        return redirect()->route('vehicle-colors.index')
            ->with('success', 'Color deleted successfully.');
    }
}
