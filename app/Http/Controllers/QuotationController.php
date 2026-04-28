<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Quotation;
use App\Services\LeadToCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations = Quotation::with(['customer', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
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
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'engine_type' => 'nullable|string|max:100',
            'transmission' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'license_plate' => 'nullable|string|max:20',
            'vin_number' => 'nullable|string|max:50',
            'service_type' => 'required',
            'service_description' => 'required|string',
            'status' => 'sometimes|in:new_lead,contacted,converted_to_customer,appointment_booked,won,lost,archived',
            'admin_notes' => 'nullable|string'
        ]);

        $quotation = Quotation::create($validated);

        // Auto-convert lead to customer + vehicle
        LeadToCustomerService::processQuotation($quotation);

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation created and customer synced successfully.');
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
            'service_type' => 'required',
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

        DB::beginTransaction();
        try {
            $quotation = Quotation::create([
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
                'status' => 'new_lead',
            ]);

            // AUTO-CREATE CUSTOMER AND VEHICLE FROM QUOTATION
            LeadToCustomerService::processQuotation($quotation);

            DB::commit();

            return redirect()->route('quotation.form')
                ->with('success', 'Thank you! Your quotation request has been submitted successfully. We will contact you within 24 hours.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('quotation.form')
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $quotation = Quotation::with(['customer', 'vehicle'])->findOrFail($id);
        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quotation = Quotation::with(['customer', 'vehicle'])->findOrFail($id);
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
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'engine_type' => 'nullable|string|max:100',
            'transmission' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'license_plate' => 'nullable|string|max:20',
            'vin_number' => 'nullable|string|max:50',
            'service_type' => 'required',
            'service_description' => 'required|string',
            'status' => 'required|in:new_lead,contacted,converted_to_customer,appointment_booked,won,lost,archived',
            'admin_notes' => 'nullable|string'
        ]);

        $quotation->update($validated);

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation updated successfully.');
    }

    /**
     * Update the status of a quotation.
     */
    public function updateStatus(Request $request, string $id)
    {
        $quotation = Quotation::findOrFail($id);

        $request->validate([
            "status" => "required|in:new_lead,contacted,converted_to_customer,appointment_booked,won,lost,archived"
        ]);

        $quotation->update([
            "status" => $request->status
        ]);

        return redirect()->route("quotations.show", $quotation->id)
            ->with("success", "Status updated successfully!");
    }

    /**
     * Convert a quotation lead to a full customer record.
     */
    public function convertToCustomer(string $id)
    {
        $quotation = Quotation::findOrFail($id);
        [$customer, $vehicle] = LeadToCustomerService::convertLead($quotation);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', "Quotation #{$quotation->id} converted to customer successfully!");
    }

    /**
     * Remove the specified resource from storage.
     */
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
        $count = Quotation::where('status', 'new_lead')->count();

        return response()->json([
            'count' => $count,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
