<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ServiceRecord;
use App\Models\CustomerNote;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesCroppedImage;

class CustomerController extends Controller
{
    use HandlesCroppedImage;

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

        // Filter by minimum number of services
        if ($request->has('min_services') && is_numeric($request->min_services)) {
            $minServices = (int)$request->min_services;
            $query->whereHas('serviceRecords', function($q) use ($minServices) {
                $q->havingRaw('COUNT(*) >= ?', [$minServices]);
            }, '>=', $minServices);
        }

        // Filter by last service date
        if ($request->has('last_service') && is_numeric($request->last_service)) {
            $days = (int)$request->last_service;
            $date = now()->subDays($days);
            $query->whereHas('serviceRecords', function($q) use ($date) {
                $q->where('service_date', '>=', $date);
            });
        }

        // Load relationships to avoid N+1 queries
        $query->withCount(['serviceRecords', 'vehicles'])
              ->withSum('serviceRecords', 'final_amount')
              ->with(['serviceRecords' => function($q) {
                  $q->latest('service_date')->limit(1);
              }]);

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
            'full_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'facebook_profile' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cropped_image'   => 'nullable|string',
            // Vehicle fields (array format for multiple vehicles)
            'vehicles' => 'nullable|array',
            'vehicles.*.make' => 'nullable|string|max:50',
            'vehicles.*.model' => 'nullable|string|max:100',
            'vehicles.*.year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicles.*.vin' => 'nullable|string|max:17',
            'vehicles.*.plate' => 'nullable|string|max:20',
            'vehicles.*.color' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Split full name into first and last name
        $nameParts = explode(' ', $request->full_name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        // Convert empty strings to null for database
        $firstName = $firstName ?: null;
        $lastName = $lastName ?: ''; // Keep as empty string, not null (last_name column is NOT NULL)
        
        // Handle profile picture (cropped or raw upload)
        // Handle profile picture (cropped or raw upload)
        $profilePicturePath = $this->saveCroppedImage($request, 'cropped_image', 'profile_picture', 'profile_pictures', 300, 85);
        
        // Create customer data array
        $customerData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'customer_since' => now(),
            'is_active' => true,
        ];
        
        // Include profile_picture
        if ($profilePicturePath) {
            $customerData['profile_picture'] = $profilePicturePath;
        }
        
        try {
            $customer = Customer::create($customerData);
        } catch (\Exception $e) {
            \Log::error('Customer creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
                'customer_data' => $customerData,
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer creation failed: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->withInput()->with('error', 'Customer creation failed: ' . $e->getMessage());
        }

        // Create vehicles if provided (array format)
        if ($request->has('vehicles') && is_array($request->vehicles)) {
            foreach ($request->vehicles as $vehicleData) {
                // Only create vehicle if at least make or model is provided
                if (!empty($vehicleData['make']) || !empty($vehicleData['model']) || 
                    !empty($vehicleData['license_plate'])) {
                    
                    Vehicle::create([
                        'customer_id' => $customer->id,
                        'make' => $vehicleData['make'] ?? null,
                        'model' => $vehicleData['model'] ?? null,
                        'year' => $vehicleData['year'] ?? null,
                        'vin' => $vehicleData['vin'] ?? null,
                        'license_plate' => $vehicleData['plate'] ?? null,
                        'color' => $vehicleData['color'] ?? null,
                        'is_active' => true,
                    ]);
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!' . 
                    ($request->has('vehicles') ? ' Vehicle(s) added.' : ''),
                'customer_id' => $customer->id,
                'redirect_url' => route('customers.show', $customer)
            ]);
        }
        
        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.' . 
                ($request->filled('vehicle_make') ? ' Vehicle added.' : ''));
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
        }, 'quotations' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        // Build unified transaction history from all modules
        $unifiedHistory = TransactionHistoryService::forCustomer($customer->id);

        // Calculate customer statistics from unified history
        $stats = [
            'total_vehicles' => $customer->vehicles->count(),
            'total_services' => count($unifiedHistory),
            'total_spent' => collect($unifiedHistory)->sum('total_amount') ?? 0,
            'average_service_cost' => count($unifiedHistory) > 0 ? collect($unifiedHistory)->sum('total_amount') / count($unifiedHistory) : 0,
            'last_service_date' => collect($unifiedHistory)->first() ? (collect($unifiedHistory)->first()->created_at ?? null) : null,
            'upcoming_services' => $customer->vehicles->where('next_service_date', '>=', now())->count(),
        ];
        
        // Load archived inspections for this customer
        $customerVehicleIds = $customer->vehicles->pluck('id')->toArray();
        
        $archivedInspections = \App\Models\Archive::where('source_module', 'inspection')
            ->where(function($q) use ($customer, $customerVehicleIds) {
                $q->whereRaw('JSON_EXTRACT(original_data, "$.customer_id") = ?', [$customer->id]);
                if (!empty($customerVehicleIds)) {
                    foreach ($customerVehicleIds as $vid) {
                        $q->orWhereRaw('JSON_EXTRACT(original_data, "$.vehicle_id") = ?', [$vid]);
                    }
                }
            })
            ->orderBy('archived_at', 'desc')
            ->get();

        // Get service history summary from unified history
        $grouped = collect($unifiedHistory)->groupBy('service_type')->map(function($items, $key) {
            return [
                'service_type' => $key ?: 'General',
                'count' => $items->count(),
                'revenue' => $items->sum('total_amount'),
            ];
        })->values();
        $serviceHistory = $grouped;

        return view('customers.show', compact('customer', 'stats', 'serviceHistory', 'archivedInspections', 'unifiedHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $customer->load('vehicles');
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'facebook_profile' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Optional vehicle fields
            'vehicle_vin' => 'nullable|string|max:17',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_color' => 'nullable|string|max:30',
            'vehicle_mileage' => 'nullable|integer|min:0',
            'engine_no' => 'nullable|string|max:50',
            'cropped_image' => 'nullable|string',
            'remove_photo' => 'boolean',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Split full name into first and last name
        $nameParts = explode(' ', $request->full_name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        // Convert empty strings to null for database
        $firstName = $firstName ?: null;
        $lastName = $lastName ?: ''; // Keep as empty string, not null (last_name column is NOT NULL)
        
        // Handle profile picture (cropped or raw upload)
        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];
        if ($request->filled('cropped_image') || $request->hasFile('profile_picture')) {
            $this->deleteStoredImage($customer->profile_picture);
            $path = $this->saveCroppedImage($request, 'cropped_image', 'profile_picture', 'profile_pictures', 300, 85);
            if ($path) {
                $updateData['profile_picture'] = $path;
            }
        }
        // Handle photo removal
        if ($request->boolean('remove_photo')) {
            $this->deleteStoredImage($customer->profile_picture);
            $updateData['profile_picture'] = null;
        }
        
        $customer->update($updateData);

        // Handle vehicle creation/update
        if ($request->filled('vehicle_make') || $request->filled('vehicle_year') || 
            $request->filled('vehicle_vin') || $request->filled('vehicle_plate') ||
            $request->filled('vehicle_color') || $request->filled('vehicle_mileage') ||
            $request->filled('engine_no')) {
            
            // Check if customer already has a vehicle
            $vehicle = $customer->vehicles()->first();
            
            if ($vehicle) {
                // Update existing vehicle
                $vehicle->update([
                    'make' => $request->vehicle_make,
                    'model' => $request->vehicle_model,
                    'year' => $request->vehicle_year,
                    'vin' => $request->vehicle_vin,
                    'license_plate' => $request->vehicle_plate,
                    'color' => $request->vehicle_color,
                    'current_mileage' => $request->vehicle_mileage,
                    'engine_no' => $request->engine_no,
                ]);
            } else {
                // Create new vehicle
                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'make' => $request->vehicle_make,
                    'model' => $request->vehicle_model,
                    'year' => $request->vehicle_year,
                    'vin' => $request->vehicle_vin,
                    'license_plate' => $request->vehicle_plate,
                    'color' => $request->vehicle_color,
                    'current_mileage' => $request->vehicle_mileage,
                    'engine_no' => $request->engine_no,
                    'is_active' => true,
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully!',
                'customer_id' => $customer->id,
                'redirect_url' => route('customers.show', $customer)
            ]);
        }
        
        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has vehicles
        if ($customer->vehicles()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with vehicles. Please delete or reassign vehicles first.');
        }
        
        // Check if customer has service records
        if ($customer->serviceRecords()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with service records. Please delete service records first.');
        }
        
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

        // Check if request wants JSON
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'vehicles' => $vehicles->map(function($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'year' => $vehicle->year,
                        'make' => $vehicle->make,
                        'model' => $vehicle->model,
                        'trim' => $vehicle->trim,
                        'license_plate' => $vehicle->license_plate,
                        'vin' => $vehicle->vin,
                        'color' => $vehicle->color,
                        'created_at' => $vehicle->created_at,
                        'updated_at' => $vehicle->updated_at
                    ];
                }),
                'total_vehicles' => $vehicles->total()
            ]);
        }

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

    /**
     * Upload profile picture via AJAX (like Facebook)
     */
    public function uploadProfilePicture(Request $request, Customer $customer)
    {
        try {
            \Log::info('Profile picture upload started for customer: ' . $customer->id);
            
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            \Log::info('File validation passed');
            
            // Delete old profile picture if exists
            if ($customer->profile_picture) {
                \Log::info('Deleting old profile picture: ' . $customer->profile_picture);
                Storage::disk('public')->delete($customer->profile_picture);
            }

            // Upload new profile picture
            \Log::info('Uploading new profile picture');
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            \Log::info('Profile picture stored at: ' . $profilePicturePath);
            
            // Update customer
            $customer->update(['profile_picture' => $profilePicturePath]);
            \Log::info('Customer record updated');

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully.',
                'profile_picture_url' => asset('storage/' . $profilePicturePath),
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile picture upload error: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'errors' => ['profile_picture' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Remove profile picture
     */
    public function removeProfilePicture(Customer $customer)
    {
        try {
            \Log::info('Removing profile picture for customer: ' . $customer->id);
            
            if ($customer->profile_picture) {
                \Log::info('Deleting profile picture: ' . $customer->profile_picture);
                Storage::disk('public')->delete($customer->profile_picture);
                $customer->update(['profile_picture' => null]);
                \Log::info('Profile picture removed successfully');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Profile picture removed successfully.',
                ]);
            }

            \Log::info('No profile picture to remove for customer: ' . $customer->id);
            return response()->json([
                'success' => false,
                'message' => 'No profile picture to remove.',
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Remove profile picture error: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Remove failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a customer form link (Google Form-like)
     */
    public function generateForm(Request $request)
    {
        try {
            // Generate a unique token for the form (valid for 7 days exactly)
            $token = \Illuminate\Support\Str::random(32);
            $now = now();
            $expiresAt = $now->copy()->addDays(7)->endOfDay(); // Expire at end of 7th day
            
            // Store form token in database (survives cache clears)
            \DB::table('customer_form_tokens')->insert([
                'token' => $token,
                'generated_by' => auth()->id(),
                'generated_at' => $now,
                'expires_at' => $expiresAt,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Generate the form URL
            $formUrl = 'https://form.fixitautoservices.com/customer-form/' . $token;
            
            // Generate QR code URL
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($formUrl);

            return response()->json([
                'success' => true,
                'message' => 'Customer form generated successfully! (Valid for 7 days)',
                'form_url' => $formUrl,
                'qr_code_url' => $qrCodeUrl,
                'token' => $token,
                'expires_at' => $expiresAt->format('F j, Y \a\t g:i A'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Generate form error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate form: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the customer form (public access)
     */
    public function showForm($token)
    {
        // Get token from database
        $formData = \DB::table('customer_form_tokens')->where('token', $token)->first();
        
        if (!$formData) {
            return view('customers.form-expired');
        }

        $expiresAt = \Carbon\Carbon::parse($formData->expires_at);

        // Check if token is expired
        if (now()->greaterThan($expiresAt)) {
            return view('customers.form-expired');
        }

        // Check if form has already been submitted (single submission per link)
        $submissionCount = Customer::where('form_token', $token)->count();
        if ($submissionCount > 0) {
            return view('customers.form-already-submitted', [
                'token' => $token,
                'expires_at' => $expiresAt->format('F j, Y \a\t g:i A'),
            ]);
        }

        return view('customers.form-public', [
            'token' => $token,
            'expires_at' => $expiresAt->format('F j, Y \a\t g:i A'),
        ]);
    }

    /**
     * Submit the customer form (public access)
     */
    public function submitForm(Request $request, $token)
    {
        // Get token from database
        $formData = \DB::table('customer_form_tokens')->where('token', $token)->first();
        
        if (!$formData) {
            return response()->json([
                'success' => false,
                'message' => 'Form link has expired or is invalid.',
            ], 400);
        }

        $expiresAt = \Carbon\Carbon::parse($formData->expires_at);

        // Check if token is expired
        if (now()->greaterThan($expiresAt)) {
            return response()->json([
                'success' => false,
                'message' => 'Form link has expired.',
            ], 400);
        }

        // Check if form has already been submitted (single submission per link)
        $submissionCount = Customer::where('form_token', $token)->count();
        if ($submissionCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This form link has already been used. Each link can only be submitted once.',
            ], 400);
        }

        // Validate form data
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'facebook_profile' => 'nullable|string|max:255',
            // Optional vehicle fields
            'vehicle_make' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_vin' => 'nullable|string|max:17',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_color' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create customer
        $nameParts = explode(' ', $request->full_name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        // Ensure last_name is not null (database constraint)
        $lastName = $lastName ?: '';

        $customer = Customer::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'facebook_profile' => $request->facebook_profile,
            'customer_since' => now(),
            'is_active' => true,
            'created_via_form' => true, // Mark as created via form
            'form_token' => $token, // Store which form was used
            'form_submitted_at' => now(), // Timestamp when form was submitted
        ]);

        // Create vehicle if provided
        if ($request->filled('vehicle_make') || $request->filled('vehicle_model') || 
            $request->filled('vehicle_year') || $request->filled('vehicle_vin') || 
            $request->filled('vehicle_plate') || $request->filled('vehicle_color')) {
            
            Vehicle::create([
                'customer_id' => $customer->id,
                'make' => $request->vehicle_make,
                'model' => $request->vehicle_model,
                'year' => $request->vehicle_year,
                'vin' => $request->vehicle_vin,
                'license_plate' => $request->vehicle_plate,
                'color' => $request->vehicle_color,
                'is_active' => true,
            ]);
        }

        // Add a note that customer was created via form
        $customer->notes()->create([
            'user_id' => $formData->generated_by, // User who generated the form
            'note_type' => 'general',
            'content' => 'Customer created via public form submission.',
            'is_important' => false,
            'tags' => ['form', 'public'],
        ]);

        // Invalidate the token after successful submission (optional)
        // \Cache::forget('customer_form_token_' . $token);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your information has been submitted successfully.',
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Show generated forms with status
     */
    public function generatedForms()
    {
        // Get all customers created via form
        $formCustomers = Customer::where('created_via_form', true)
            ->orderBy('form_submitted_at', 'desc')
            ->get();

        // Get all form tokens from database (survives cache clears)
        $dbTokens = \DB::table('customer_form_tokens')
            ->orderBy('generated_at', 'desc')
            ->get();
        
        $activeForms = [];
        foreach ($dbTokens as $formData) {
            // Count submissions for this token
            $submissionCount = Customer::where('form_token', $formData->token)->count();
            
            // Get user who generated the form
            $generatedBy = $formData->generated_by ? \App\Models\User::find($formData->generated_by) : null;
            
            $activeForms[] = [
                'token' => $formData->token,
                'generated_at' => \Carbon\Carbon::parse($formData->generated_at),
                'generated_by' => $formData->generated_by,
                'expires_at' => \Carbon\Carbon::parse($formData->expires_at),
                'submission_count' => $submissionCount,
                'form_url' => 'https://form.fixitautoservices.com/customer-form/' . $formData->token,
                'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode('https://form.fixitautoservices.com/customer-form/' . $formData->token),
                'is_expired' => now()->greaterThan($formData->expires_at),
            ];
        }

        return view('customers.generated-forms', [
            'formCustomers' => $formCustomers,
            'activeForms' => $activeForms,
        ]);
    }

    /**
     * Get form details for API
     */
    public function formDetails($token)
    {
        $formData = \DB::table('customer_form_tokens')->where('token', $token)->first();
        
        if (!$formData) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found or expired',
            ], 404);
        }

        // Count submissions for this token
        $submissionCount = Customer::where('form_token', $token)->count();
        
        // Get user who generated the form
        $generatedBy = $formData->generated_by ? \App\Models\User::find($formData->generated_by) : null;
        
        $formDetails = [
            'token' => $formData->token,
            'generated_at' => \Carbon\Carbon::parse($formData->generated_at)->format('F j, Y \a\t g:i A'),
            'generated_by' => $generatedBy ? $generatedBy->name : 'System',
            'expires_at' => \Carbon\Carbon::parse($formData->expires_at)->format('F j, Y \a\t g:i A'),
            'submission_count' => $submissionCount,
            'form_url' => 'https://form.fixitautoservices.com/customer-form/' . $formData->token,
            'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode('https://form.fixitautoservices.com/customer-form/' . $formData->token),
            'is_expired' => now()->greaterThan($formData->expires_at),
        ];

        return response()->json([
            'success' => true,
            'form' => $formDetails,
        ]);
    }

    /**
     * API: Smart customer search with progressive loading.
     */
    public function apiSearch(Request $request)
    {
        $query = Customer::query()->whereNull('customers.deleted_at');
        $search = $request->get('q', '');
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $filters = $request->get('filters', []);

        // === SMART SEARCH (cross-field, partial match) ===
        if (!empty($search) && strlen(trim($search)) >= 1) {
            $terms = explode(' ', trim($search));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $q->where(function ($sub) use ($like, $term) {
                        $sub->where('customers.first_name', 'LIKE', $like)
                            ->orWhere('customers.last_name', 'LIKE', $like)
                            ->orWhere('customers.email', 'LIKE', $like)
                            ->orWhere('customers.phone', 'LIKE', $like)
                            ->orWhere('customers.address', 'LIKE', $like)
                            ->orWhere('customers.city', 'LIKE', $like)
                            ->orWhere('customers.notes', 'LIKE', $like)
                            ->orWhereHas('vehicles', function ($v) use ($like, $term) {
                                $v->where(function ($vq) use ($like, $term) {
                                    $vq->where('license_plate', 'LIKE', $like)
                                        ->orWhere('make', 'LIKE', $like)
                                        ->orWhere('model', 'LIKE', $like)
                                        ->orWhere('vin', 'LIKE', $like)
                                        ->orWhere('color', 'LIKE', $like)
                                        ->orWhere('engine_type', 'LIKE', $like)
                                        ->orWhere('engine_no', 'LIKE', $like)
                                        ->orWhere('year', 'LIKE', $like);
                                });
                            })
                            ->orWhereHas('serviceRecords', function ($sr) use ($like) {
                                $sr->where('service_type', 'LIKE', $like);
                            })
                            ->orWhereHas('invoices', function ($inv) use ($like) {
                                $inv->where('invoice_number', 'LIKE', $like);
                            });
                    });
                }
            });
        }

        // === FILTERS ===
        if (!empty($filters)) {
            $this->applySearchFilters($query, $filters);
        }

        // === SEARCH RANKING ===
        if (!empty($search) && strlen(trim($search)) >= 1) {
            $query->orderByRaw(
                "CASE 
                    WHEN CONCAT(customers.first_name, ' ', customers.last_name) = ? THEN 0
                    WHEN customers.first_name LIKE ? OR customers.last_name LIKE ? THEN 1
                    WHEN customers.phone LIKE ? THEN 2
                    WHEN customers.email LIKE ? THEN 3
                    ELSE 4
                END",
                [$search, $search . '%', $search . '%', $search . '%', $search . '%']
            );
        }

        // Recent & frequent customers prioritized
        $query->orderBy('customers.updated_at', 'desc');

        // === LOAD RELATIONSHIPS ===
        $query->withCount(['serviceRecords', 'vehicles'])
            ->withSum('serviceRecords', 'final_amount')
            ->with(['vehicles', 'serviceRecords' => function ($sr) {
                $sr->latest('service_date')->limit(1);
            }]);

        // === PAGINATION (progressive loading) ===
        $total = $query->count();
        $customers = $query->skip(($page - 1) * $perPage)
            ->take($perPage + 1)
            ->get();

        $hasMore = $customers->count() > $perPage;
        if ($hasMore) {
            $customers = $customers->take($perPage);
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        $results = $customers->map(function ($customer) {
            $lastService = $customer->serviceRecords->first();
            $primaryVehicle = $customer->vehicles->first();

            $hasUnpaid = $customer->invoices()
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->where('balance_due', '>', 0)
                ->exists();

            return [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'city' => $customer->city,
                'avatar' => $customer->avatar,
                'has_profile_picture' => $customer->has_profile_picture,
                'is_active' => (bool) $customer->is_active,
                'balance' => (float) $customer->balance,
                'has_unpaid' => $hasUnpaid,
                'vehicles_count' => (int) ($customer->vehicles_count ?? $customer->vehicles->count()),
                'service_records_count' => (int) ($customer->service_records_count ?? 0),
                'total_spent' => (float) ($customer->service_records_sum_final_amount ?? 0),
                'last_service_date' => $lastService ? $lastService->service_date->format('Y-m-d') : null,
                'last_service_type' => $lastService ? $lastService->service_type : null,
                'customer_since' => $customer->customer_since ? $customer->customer_since->format('Y-m-d') : null,
                'vehicles' => $customer->vehicles->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'make' => $v->make,
                        'model' => $v->model,
                        'year' => $v->year,
                        'license_plate' => $v->license_plate,
                        'color' => $v->color,
                    ];
                }),
                'show_url' => route('customers.show', $customer),
                'edit_url' => route('customers.edit', $customer),
            ];
        });

        return response()->json([
            'customers' => $results,
            'has_more' => $hasMore,
            'page' => $page,
            'total' => $total,
            'total_pages' => $totalPages,
        ]);
    }

    /**
     * API: Autocomplete suggestions.
     */
    public function apiAutocomplete(Request $request)
    {
        $term = $request->get('q', '');
        if (strlen(trim($term)) < 1) {
            return response()->json([]);
        }

        $like = '%' . $term . '%';
        $suggestions = collect();

        // Names
        $names = Customer::whereNull('deleted_at')
            ->where(function ($q) use ($like, $term) {
                $q->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })
            ->selectRaw("CONCAT(first_name, ' ', last_name) as value, 'name' as type")
            ->distinct()
            ->limit(5)
            ->pluck('value');

        foreach ($names as $n) {
            $suggestions->push(['value' => $n, 'type' => 'name', 'label' => 'Customer']);
        }

        // Plates
        $plates = Vehicle::whereNull('deleted_at')
            ->where('license_plate', 'LIKE', $like)
            ->whereNotNull('license_plate')->where('license_plate', '!=', '')
            ->selectRaw("DISTINCT license_plate as value, 'plate' as type")
            ->limit(5)
            ->pluck('value');

        foreach ($plates as $p) {
            $suggestions->push(['value' => $p, 'type' => 'plate', 'label' => 'Plate #']);
        }

        // Models
        $models = Vehicle::whereNull('deleted_at')
            ->where('model', 'LIKE', $like)
            ->whereNotNull('model')->where('model', '!=', '')
            ->selectRaw("DISTINCT model as value, 'model' as type")
            ->limit(5)
            ->pluck('value');

        foreach ($models as $m) {
            $suggestions->push(['value' => $m, 'type' => 'model', 'label' => 'Vehicle Model']);
        }

        // Locations (cities)
        $locations = Customer::whereNull('deleted_at')
            ->where('city', 'LIKE', $like)
            ->whereNotNull('city')->where('city', '!=', '')
            ->selectRaw("DISTINCT city as value, 'location' as type")
            ->limit(5)
            ->pluck('value');

        foreach ($locations as $l) {
            $suggestions->push(['value' => $l, 'type' => 'location', 'label' => 'Location']);
        }

        return response()->json($suggestions->take(10)->values());
    }

    /**
     * API: Get filter options (brands, models, locations).
     */
    public function apiFilterOptions()
    {
        $brands = Vehicle::whereNull('deleted_at')
            ->whereNotNull('make')->where('make', '!=', '')
            ->select('make as value', 'make as label')
            ->distinct()
            ->orderBy('make')
            ->get();

        $models = Vehicle::whereNull('deleted_at')
            ->whereNotNull('model')->where('model', '!=', '')
            ->select('model as value', 'model as label')
            ->distinct()
            ->orderBy('model')
            ->get();

        $locations = Customer::whereNull('deleted_at')
            ->whereNotNull('city')->where('city', '!=', '')
            ->select('city as value', 'city as label')
            ->distinct()
            ->orderBy('city')
            ->get();

        return response()->json([
            'brands' => $brands,
            'models' => $models,
            'locations' => $locations,
        ]);
    }

    /**
     * API: Get vehicles for a customer (used by JS loadVehicles function).
     */
    public function apiCustomerVehicles()
    {
        $customerId = request('customer_id');
        if (!$customerId) {
            return response()->json([]);
        }
        
        $vehicles = Vehicle::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'year' => $v->year,
                    'make' => $v->make,
                    'model' => $v->model,
                    'license_plate' => $v->license_plate,
                    'color' => $v->color,
                    'odometer' => $v->odometer,
                ];
            });
        
        return response()->json($vehicles);
    }

    /**
     * Apply optional filters to the search query.
     */
    private function applySearchFilters($query, $filters)
    {
        if (!empty($filters['brand'])) {
            $query->whereHas('vehicles', function ($v) use ($filters) {
                $v->where('make', $filters['brand']);
            });
        }
        if (!empty($filters['model'])) {
            $query->whereHas('vehicles', function ($v) use ($filters) {
                $v->where('model', $filters['model']);
            });
        }
        if (!empty($filters['location'])) {
            $query->where('city', 'LIKE', '%' . $filters['location'] . '%');
        }
        if (!empty($filters['last_service_days'])) {
            $days = (int) $filters['last_service_days'];
            $query->whereHas('serviceRecords', function ($sr) use ($days) {
                $sr->where('service_date', '>=', now()->subDays($days));
            });
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active'] ? 1 : 0);
        }
        if (!empty($filters['unpaid_balance'])) {
            $query->whereHas('invoices', function ($inv) {
                $inv->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->where('balance_due', '>', 0);
            });
        }
        if (!empty($filters['frequent'])) {
            $query->has('serviceRecords', '>=', 5);
        }
        if (!empty($filters['new_customers'])) {
            $query->where('created_at', '>=', now()->subDays(30));
        }
    }

    /**
     * Get the latest quotation for a customer (for auto-fill).
     */
    public function apiLatestQuotation($customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(null);
        }

        $quotation = $customer->quotations()->latest()->first();
        if (!$quotation) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $quotation->id,
            'service_description' => $quotation->service_description,
            'vehicle_make' => $quotation->vehicle_make,
            'vehicle_model' => $quotation->vehicle_model,
            'vehicle_year' => $quotation->vehicle_year,
            'license_plate' => $quotation->license_plate,
            'service_type' => $quotation->service_type,
            'budget_min' => $quotation->budget_min,
            'budget_max' => $quotation->budget_max,
            'status' => $quotation->status,
            'created_at' => $quotation->created_at->format('M j, Y'),
        ]);
    }

    /**
     * Archive (soft-delete) a customer. Admin/super_admin only.
     */
    public function archive(Customer $customer)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return redirect()->route('customers.index')
                ->with('error', 'Only administrators can archive customers.');
        }

        try {
            \App\Services\ArchiveService::archive($customer, 'customer');

            return redirect()->route('customers.index')
                ->with('success', 'Customer archived successfully.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Failed to archive customer: ' . $e->getMessage());
        }
    }
}
