<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with('customer')->orderBy('created_at', 'desc');
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhere('engine_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }
        
        $vehicles = $query->paginate(20);
        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('is_active', true)->count();
        $vehiclesWithVin = Vehicle::whereNotNull('vin')->count();
        $vehiclesWithEngineNo = Vehicle::whereNotNull('engine_no')->count();
        
        return view('vehicles.index', compact('vehicles', 'totalVehicles', 'activeVehicles', 'vehiclesWithVin', 'vehiclesWithEngineNo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = \App\Models\Customer::orderBy('first_name')->get();
        $selectedCustomerId = $request->get('customer_id');
        $selectedCustomer = null;
        
        if ($selectedCustomerId) {
            $selectedCustomer = \App\Models\Customer::find($selectedCustomerId);
        }
        
        return view('vehicles.create', compact('customers', 'selectedCustomerId', 'selectedCustomer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'nullable|string|max:20',
            'vin' => [
                'nullable',
                'string',
                'max:17',
                function ($attribute, $value, $fail) {
                    // Check if VIN is provided and not empty
                    if (!empty($value)) {
                        // Check if VIN already exists in database
                        $existingVehicle = \App\Models\Vehicle::where('vin', $value)->first();
                        if ($existingVehicle) {
                            $fail('This VIN already exists in the system. Please use a different VIN or leave it blank.');
                        }
                    }
                },
            ],
            'color' => 'nullable|string|max:30',
            'vehicle_type' => 'nullable|string|max:50|in:car,truck,suv,van,motorcycle,commercial',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        // Handle photo upload
        $photoPath = null;
        $photoName = null;
        
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->storeAs('vehicle_photos', $photoName, 'public');
        }

        // Set default values for fields not in the form but required by database
        // Handle vehicle_type: if not provided or empty, use default 'car'
        $vehicleType = isset($validated['vehicle_type']) && !empty($validated['vehicle_type']) 
            ? $validated['vehicle_type'] 
            : 'car';
        
        $vehicleData = array_merge($validated, [
            'vehicle_type' => $vehicleType,
            'odometer' => 0,
            'service_interval_miles' => 5000,
            'service_interval_months' => 6,
            'average_service_cost' => 0,
            'total_service_count' => 0,
            'has_warranty' => false,
            'has_recall' => false,
            'is_active' => true,
            'photo' => $photoName,
            'photo_path' => $photoPath,
        ]);

        try {
            // First, ensure the brand exists in our vehicle_brands table
            $brandName = trim($validated['make']);
            $modelName = trim($validated['model']);
            
            // Find or create the brand
            $brand = \App\Models\VehicleBrand::firstOrCreate(
                ['name' => $brandName],
                ['is_active' => true]
            );
            
            // Find or create the model for this brand
            $vehicleModel = \App\Models\VehicleModel::firstOrCreate(
                [
                    'vehicle_brand_id' => $brand->id,
                    'name' => $modelName
                ],
                [
                    'year_start' => $validated['year'],
                    'year_end' => $validated['year'],
                    'vehicle_type' => $vehicleType,
                    'is_active' => true
                ]
            );
            
            // Find or create the color in our reference table
            if (!empty($validated['color'])) {
                $colorName = trim($validated['color']);
                $color = \App\Models\VehicleColor::firstOrCreate(
                    ['name' => $colorName],
                    ['is_active' => true]
                );
            }
            
            // Increment popularity scores
            $brand->incrementPopularity();
            $vehicleModel->incrementPopularity();
            
            // Create the vehicle
            $vehicle = \App\Models\Vehicle::create($vehicleData);
            
        } catch (\Exception $e) {
            // Check if it's a duplicate entry error
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'vehicles_vin_unique')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['vin' => 'This VIN already exists in the system. Please use a different VIN or leave it blank.']);
            }
            
            // For other database errors, show a generic error
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while saving the vehicle. Please try again.']);
        }

        // Check if we came from a customer page (has referrer or customer_id in session)
        // For now, always redirect to customer page since that's where users add vehicles from
        return redirect()->route('customers.show', $validated['customer_id'])
            ->with('success', 'Vehicle added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['customer', 'serviceRecords', 'appointments']);
        
        // Build unified transaction history from all modules
        $unifiedHistory = TransactionHistoryService::forVehicle($vehicle->id);
        
        // Load archived inspections for this vehicle
        $archivedInspections = \App\Models\Archive::where('source_module', 'inspection')
            ->whereRaw('JSON_EXTRACT(original_data, "$.vehicle_id") = ?', [$vehicle->id])
            ->orderBy('archived_at', 'desc')
            ->get();
        
        return view('vehicles.show', compact('vehicle', 'archivedInspections', 'unifiedHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        $customers = \App\Models\Customer::orderBy('first_name')->get();
        return view('vehicles.edit', compact('vehicle', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'nullable|string|max:20',
            'vin' => [
                'nullable',
                'string',
                'max:17',
                function ($attribute, $value, $fail) use ($vehicle) {
                    // Check if VIN is provided and not empty
                    if (!empty($value)) {
                        // Check if VIN already exists in database (excluding current vehicle)
                        $existingVehicle = \App\Models\Vehicle::where('vin', $value)
                            ->where('id', '!=', $vehicle->id)
                            ->first();
                        if ($existingVehicle) {
                            $fail('This VIN already exists in the system. Please use a different VIN or leave it blank.');
                        }
                    }
                },
            ],
            'color' => 'nullable|string|max:30',
            'vehicle_type' => 'nullable|string|max:50|in:car,truck,suv,van,motorcycle,commercial',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'is_active' => 'boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photoPath = $photo->storeAs('vehicle_photos', $photoName, 'public');
            
            // Delete old photo if exists
            if ($vehicle->photo_path) {
                \Storage::disk('public')->delete($vehicle->photo_path);
            }
            
            $validated['photo'] = $photoName;
            $validated['photo_path'] = $photoPath;
        }

        // Handle vehicle_type: if not provided or empty, use default 'car'
        if (isset($validated['vehicle_type']) && !empty($validated['vehicle_type'])) {
            $vehicleType = $validated['vehicle_type'];
        } else {
            $vehicleType = 'car';
            $validated['vehicle_type'] = $vehicleType;
        }

        try {
            // Update the vehicle
            $vehicle->update($validated);
            
            // Also update the brand/model in our reference tables
            $brandName = trim($validated['make']);
            $modelName = trim($validated['model']);
            
            // Find or create the brand
            $brand = \App\Models\VehicleBrand::firstOrCreate(
                ['name' => $brandName],
                ['is_active' => true]
            );
            
            // Find or create the model for this brand
            $vehicleModel = \App\Models\VehicleModel::firstOrCreate(
                [
                    'vehicle_brand_id' => $brand->id,
                    'name' => $modelName
                ],
                [
                    'year_start' => $validated['year'],
                    'year_end' => $validated['year'],
                    'vehicle_type' => $vehicleType,
                    'is_active' => true
                ]
            );
            
            // Find or create the color in our reference table
            if (!empty($validated['color'])) {
                $colorName = trim($validated['color']);
                $color = \App\Models\VehicleColor::firstOrCreate(
                    ['name' => $colorName],
                    ['is_active' => true]
                );
            }
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An error occurred while updating the vehicle. Please try again.']);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            $vehicle->delete();
            return redirect()->route('vehicles.index')
                ->with('success', 'Vehicle deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('vehicles.index')
                ->with('error', 'An error occurred while deleting the vehicle. Please try again.');
        }
    }
}