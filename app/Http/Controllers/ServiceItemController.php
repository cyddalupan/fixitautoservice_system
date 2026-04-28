<?php

namespace App\Http\Controllers;

use App\Models\ServiceItem;
use Illuminate\Http\Request;

class ServiceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceItems = ServiceItem::orderBy('name')->paginate(20);
        return view('service-items.index', compact('serviceItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('service-items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'retail_price' => 'required|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'category_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ServiceItem::create($validated);

        return redirect()->route('service-items.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceItem $serviceItem)
    {
        // Not needed for now, redirect to edit
        return redirect()->route('service-items.edit', $serviceItem);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceItem $serviceItem)
    {
        return view('service-items.edit', compact('serviceItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceItem $serviceItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'retail_price' => 'required|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'category_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $serviceItem->update($validated);

        return redirect()->route('service-items.index')
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceItem $serviceItem)
    {
        $serviceItem->delete();

        return redirect()->route('service-items.index')
            ->with('success', 'Service deleted successfully.');
    }
}
