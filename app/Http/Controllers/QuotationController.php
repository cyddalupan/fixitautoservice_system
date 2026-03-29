<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations = Quotation::orderBy('created_at', 'desc')->paginate(20);
        return view('quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('quotations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'service_type' => 'required|string|max:100',
            'service_description' => 'required|string',
            'status' => 'sometimes|in:pending,reviewed,contacted,converted,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        Quotation::create($validated);

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation created successfully.');
    }

    /**
     * Store a public quotation submission from website.
     */
    public function storePublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'nullable|string|max:20',
            'vin_number' => 'nullable|string|max:50',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time' => 'nullable|string|in:morning,afternoon,evening,anytime',
            'service_type' => 'required|string|max:100',
            'service_checklist' => 'nullable|array',
            'service_checklist.*' => 'string|max:100',
            'parts_preference' => 'nullable|string|in:oem,aftermarket,no_preference',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'service_description' => 'required|string',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'consent_contact' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return redirect()->route('quotation.form')
                ->withErrors($validator)
                ->withInput();
        }

        // Handle photo uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('quotation_photos', 'public');
                $photoPaths[] = $path;
            }
        }

        Quotation::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'vehicle_make' => $request->vehicle_make,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_year' => $request->vehicle_year,
            'license_plate' => $request->license_plate,
            'vin_number' => $request->vin_number,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'service_type' => $request->service_type,
            'service_checklist' => $request->service_checklist,
            'parts_preference' => $request->parts_preference,
            'budget_min' => $request->budget_min,
            'budget_max' => $request->budget_max,
            'service_description' => $request->service_description,
            'photos' => $photoPaths,
            'consent_contact' => (bool)$request->consent_contact,
            'status' => 'pending'
        ]);

        return redirect()->route('quotation.form')
            ->with('success', 'Thank you! Your quotation request has been submitted successfully. We will contact you within 24 hours.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $quotation = Quotation::findOrFail($id);
        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quotation = Quotation::findOrFail($id);
        return view('quotations.edit', compact('quotation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $quotation = Quotation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'service_type' => 'required|string|max:100',
            'service_description' => 'required|string',
            'status' => 'required|in:pending,reviewed,contacted,converted,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $quotation->update($validated);

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Update the status of a quotation.
     */
    public function updateStatus(Request $request, string $id)
    {
        $quotation = Quotation::findOrFail($id);
        
        $request->validate([
            "status" => "required|in:pending,reviewed,contacted,converted,rejected"
        ]);
        
        $quotation->update([
            "status" => $request->status
        ]);
        
        return redirect()->route("quotations.show", $quotation->id)
            ->with("success", "Status updated successfully!");
    }
    public function destroy(string $id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }
    
    /**
     * Get count of pending quotations for AJAX requests
     */
    public function pendingCount()
    {
        $count = Quotation::where('status', 'pending')->count();
        
        return response()->json([
            'count' => $count,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
