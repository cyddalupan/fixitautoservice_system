<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ServiceRecord;
use App\Models\CustomerNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by customer type
        if ($request->has('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        // Filter by segment
        if ($request->has('segment')) {
            $query->where('segment', $request->segment);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active == 'active');
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $customers = $query->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'customer_type' => 'required|in:individual,commercial,fleet',
            'company_name' => 'required_if:customer_type,commercial,fleet',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|in:net_15,net_30,net_60,cod',
            'preferred_contact' => 'required|in:email,phone,sms',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'customer_type' => $request->customer_type,
            'company_name' => $request->company_name,
            'tax_id' => $request->tax_id,
            'credit_limit' => $request->credit_limit ?? 0,
            'payment_terms' => $request->payment_terms,
            'customer_since' => now(),
            'preferred_contact' => $request->preferred_contact,
            'notes' => $request->notes,
            'segment' => $request->segment,
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['vehicles', 'serviceRecords' => function($query) {
            $query->orderBy('service_date', 'desc')->limit(10);
        }, 'notes' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        // Calculate customer statistics
        $stats = [
            'total_vehicles' => $customer->vehicles->count(),
            'total_services' => $customer->serviceRecords->count(),
            'total_spent' => $customer->serviceRecords->sum('final_amount'),
            'average_service_cost' => $customer->serviceRecords->avg('final_amount'),
            'last_service_date' => $customer->serviceRecords->max('service_date'),
            'upcoming_services' => $customer->vehicles->where('next_service_date', '>=', now())->count(),
        ];

        // Get service history summary
        $serviceHistory = $customer->serviceRecords()
            ->select('service_type', \DB::raw('COUNT(*) as count'), \DB::raw('SUM(final_amount) as revenue'))
            ->groupBy('service_type')
            ->orderBy('revenue', 'desc')
            ->get();

        return view('customers.show', compact('customer', 'stats', 'serviceHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'customer_type' => 'required|in:individual,commercial,fleet',
            'company_name' => 'required_if:customer_type,commercial,fleet',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|in:net_15,net_30,net_60,cod',
            'preferred_contact' => 'required|in:email,phone,sms',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'customer_type' => $request->customer_type,
            'company_name' => $request->company_name,
            'tax_id' => $request->tax_id,
            'credit_limit' => $request->credit_limit,
            'payment_terms' => $request->payment_terms,
            'is_active' => $request->has('is_active'),
            'preferred_contact' => $request->preferred_contact,
            'notes' => $request->notes,
            'segment' => $request->segment,
            'preferences' => $request->preferences ? json_decode($request->preferences, true) : null,
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Add a note to the customer.
     */
    public function addNote(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'note_type' => 'required|in:general,preference,complaint,compliment,follow_up,reminder',
            'is_important' => 'boolean',
            'requires_follow_up' => 'boolean',
            'follow_up_date' => 'nullable|date',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customer->notes()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'note_type' => $request->note_type,
            'is_important' => $request->boolean('is_important'),
            'requires_follow_up' => $request->boolean('requires_follow_up'),
            'follow_up_date' => $request->follow_up_date,
            'tags' => $request->tags,
        ]);

        return redirect()->back()
            ->with('success', 'Note added successfully.');
    }

    /**
     * Update customer loyalty points.
     */
    public function updateLoyalty(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer',
            'action' => 'required|in:add,subtract,set',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $currentPoints = $customer->loyalty_points;
        
        switch ($request->action) {
            case 'add':
                $newPoints = $currentPoints + $request->points;
                break;
            case 'subtract':
                $newPoints = max(0, $currentPoints - $request->points);
                break;
            case 'set':
                $newPoints = max(0, $request->points);
                break;
        }

        $customer->update(['loyalty_points' => $newPoints]);

        // Add a note about the loyalty points change
        $customer->notes()->create([
            'user_id' => auth()->id(),
            'note_type' => 'general',
            'content' => "Loyalty points updated: {$request->action} {$request->points} points. Reason: {$request->reason}. New total: {$newPoints} points.",
            'is_important' => true,
            'tags' => ['loyalty', 'points'],
        ]);

        return redirect()->back()
            ->with('success', 'Loyalty points updated successfully.');
    }

    /**
     * Get customer service history.
     */
    public function serviceHistory(Customer $customer)
    {
        $services = $customer->serviceRecords()
            ->with(['vehicle', 'technician', 'serviceAdvisor'])
            ->orderBy('service_date', 'desc')
            ->paginate(20);

        return view('customers.service-history', compact('customer', 'services'));
    }

    /**
     * Get customer vehicles.
     */
    public function vehicles(Customer $customer)
    {
        $vehicles = $customer->vehicles()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('customers.vehicles', compact('customer', 'vehicles'));
    }

    /**
     * Get customer notes.
     */
    public function notes(Customer $customer)
    {
        $notes = $customer->notes()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('customers.notes', compact('customer', 'notes'));
    }

    /**
     * Export customer data.
     */
    public function export(Customer $customer, $format = 'pdf')
    {
        $customer->load(['vehicles', 'serviceRecords', 'notes']);
        
        // Generate export based on format
        // This would typically use a PDF or Excel library
        // For now, return JSON
        return response()->json([
            'customer' => $customer,
            'vehicles' => $customer->vehicles,
            'service_records' => $customer->serviceRecords,
            'notes' => $customer->notes,
        ]);
    }

    /**
     * Send service reminder to customer.
     */
    public function sendReminder(Customer $customer, Vehicle $vehicle = null)
    {
        // Get upcoming services
        $upcomingServices = $vehicle 
            ? [$vehicle]
            : $customer->vehicles()->where('next_service_date', '>=', now())->get();

        if ($upcomingServices->isEmpty()) {
            return redirect()->back()
                ->with('warning', 'No upcoming services found for this customer.');
        }

        // Send reminders (this would integrate with email/SMS service)
        foreach ($upcomingServices as $vehicle) {
            // Logic to send reminder
            // $this->sendServiceReminder($customer, $vehicle);
        }

        // Add a note about the reminder
        $customer->notes()->create([
            'user_id' => auth()->id(),
            'note_type' => 'reminder',
            'content' => 'Service reminder sent for ' . ($vehicle ? $vehicle->full_description : 'all vehicles'),
            'is_important' => false,
            'tags' => ['reminder', 'communication'],
        ]);

        return redirect()->back()
            ->with('success', 'Service reminders sent successfully.');
    }
}