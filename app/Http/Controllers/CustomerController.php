<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ServiceRecord;
use App\Models\CustomerNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            // Optional vehicle fields for immediate vehicle addition
            'vehicle_make' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_vin' => 'nullable|string|max:17',
            'vehicle_plate' => 'nullable|string|max:20',
            'vehicle_color' => 'nullable|string|max:30',
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
        
        // Handle profile picture upload
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }
        
        $customer = Customer::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'facebook_profile' => $request->facebook_profile,
            'profile_picture' => $profilePicturePath,
            'customer_since' => now(),
            'is_active' => true,
        ]);

        // Create vehicle if vehicle fields are provided
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!' . 
                    ($request->filled('vehicle_make') ? ' Vehicle added.' : ''),
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
            // Optional service needed field (can be added in edit)
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
        
        // Handle profile picture upload
        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'facebook_profile' => $request->facebook_profile,
        ];
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($customer->profile_picture) {
                Storage::disk('public')->delete($customer->profile_picture);
            }
            
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $updateData['profile_picture'] = $profilePicturePath;
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
            // Generate a unique token for the form
            $token = \Illuminate\Support\Str::random(32);
            
            // Store form token in session or cache (valid for 3 days)
            \Cache::put('customer_form_token_' . $token, [
                'generated_at' => now(),
                'generated_by' => auth()->id(),
                'expires_at' => now()->addDays(3),
            ], now()->addDays(3));

            // Store token key in list for tracking
            $cacheKeys = \Cache::get('customer_form_tokens', []);
            $cacheKeys[] = $token;
            \Cache::put('customer_form_tokens', $cacheKeys, now()->addDays(30));

            // Generate the form URL - Use main website domain for public access
            $formUrl = 'https://form.fixitautoservices.com/customer-form/' . $token;
            
            // Generate a QR code URL for easy mobile access
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($formUrl);

            return response()->json([
                'success' => true,
                'message' => 'Customer form generated successfully!',
                'form_url' => $formUrl,
                'qr_code_url' => $qrCodeUrl,
                'token' => $token,
                'expires_at' => now()->addDays(3)->format('F j, Y \a\t g:i A'),
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
        // Verify token exists and is valid
        $formData = \Cache::get('customer_form_token_' . $token);
        
        if (!$formData) {
            return view('customers.form-expired');
        }

        // Check if token is expired
        if (now()->greaterThan($formData['expires_at'])) {
            \Cache::forget('customer_form_token_' . $token);
            return view('customers.form-expired');
        }

        // Check if form has already been submitted (single submission per link)
        $submissionCount = Customer::where('form_token', $token)->count();
        if ($submissionCount > 0) {
            return view('customers.form-already-submitted', [
                'token' => $token,
                'expires_at' => $formData['expires_at']->format('F j, Y \a\t g:i A'),
            ]);
        }

        return view('customers.form-public', [
            'token' => $token,
            'expires_at' => $formData['expires_at']->format('F j, Y \a\t g:i A'),
        ]);
    }

    /**
     * Submit the customer form (public access)
     */
    public function submitForm(Request $request, $token)
    {
        // Verify token exists and is valid
        $formData = \Cache::get('customer_form_token_' . $token);
        
        if (!$formData) {
            return response()->json([
                'success' => false,
                'message' => 'Form link has expired or is invalid.',
            ], 400);
        }

        // Check if token is expired
        if (now()->greaterThan($formData['expires_at'])) {
            \Cache::forget('customer_form_token_' . $token);
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
            'user_id' => $formData['generated_by'], // User who generated the form
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

        // Get all active form tokens from cache
        $activeForms = [];
        $cacheKeys = \Cache::get('customer_form_tokens', []);
        
        foreach ($cacheKeys as $token) {
            $formData = \Cache::get('customer_form_token_' . $token);
            if ($formData) {
                // Count submissions for this token
                $submissionCount = Customer::where('form_token', $token)->count();
                
                $activeForms[] = [
                    'token' => $token,
                    'generated_at' => $formData['generated_at'],
                    'generated_by' => $formData['generated_by'],
                    'expires_at' => $formData['expires_at'],
                    'submission_count' => $submissionCount,
                    'form_url' => 'https://form.fixitautoservices.com/customer-form/' . $token,
                    'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode('https://form.fixitautoservices.com/customer-form/' . $token),
                    'is_expired' => now()->greaterThan($formData['expires_at']),
                ];
            }
        }

        // Sort active forms by generated_at (newest first)
        usort($activeForms, function($a, $b) {
            return $b['generated_at'] <=> $a['generated_at'];
        });

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
        $formData = \Cache::get('customer_form_token_' . $token);
        
        if (!$formData) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found or expired',
            ], 404);
        }

        // Count submissions for this token
        $submissionCount = Customer::where('form_token', $token)->count();
        
        // Get user who generated the form
        $generatedBy = \App\Models\User::find($formData['generated_by']);
        
        $formDetails = [
            'token' => $token,
            'generated_at' => $formData['generated_at']->format('F j, Y \a\t g:i A'),
            'generated_by' => $generatedBy ? $generatedBy->name : 'Unknown',
            'expires_at' => $formData['expires_at']->format('F j, Y \a\t g:i A'),
            'submission_count' => $submissionCount,
            'form_url' => 'https://form.fixitautoservices.com/customer-form/' . $token,
            'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode('https://form.fixitautoservices.com/customer-form/' . $token),
            'is_expired' => now()->greaterThan($formData['expires_at']),
        ];

        return response()->json([
            'success' => true,
            'form' => $formDetails,
        ]);
    }
}