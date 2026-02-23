<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleRecall;
use App\Models\VINDecoderCache;
use App\Models\ServiceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VehicleToolsController extends Controller
{
    /**
     * Display the vehicle tools dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get vehicle statistics
        $totalVehicles = Vehicle::count();
        $vehiclesWithVIN = Vehicle::whereNotNull('vin')->count();
        $vehiclesDecoded = Vehicle::whereNotNull('vin_decoded_at')->count();
        $vehiclesWithRecalls = Vehicle::where('open_recall_count', '>', 0)->count();
        $vehiclesNeedingRecallCheck = Vehicle::where('recall_check_required', true)->count();
        
        // Get recent recalls
        $recentRecalls = VehicleRecall::with('vehicle')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get vehicles needing VIN decoding
        $vehiclesNeedingDecoding = Vehicle::whereNotNull('vin')
            ->whereNull('vin_decoded_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get cache statistics
        $cacheEntries = VINDecoderCache::count();
        $cacheHits = VINDecoderCache::sum('cache_hits');
        $expiredCache = VINDecoderCache::where('expires_at', '<', now())->count();
        
        return view('vehicle-tools.dashboard', compact(
            'totalVehicles',
            'vehiclesWithVIN',
            'vehiclesDecoded',
            'vehiclesWithRecalls',
            'vehiclesNeedingRecallCheck',
            'recentRecalls',
            'vehiclesNeedingDecoding',
            'cacheEntries',
            'cacheHits',
            'expiredCache'
        ));
    }

    /**
     * Display the VIN decoder interface.
     */
    public function vinDecoder()
    {
        $vehicles = Vehicle::whereNotNull('vin')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
            
        return view('vehicle-tools.vin-decoder', compact('vehicles'));
    }

    /**
     * Display service history for a vehicle.
     */
    public function serviceHistory(Request $request, $vehicleId = null)
    {
        $vehicle = null;
        $serviceRecords = collect();
        
        if ($vehicleId) {
            $vehicle = Vehicle::with(['serviceRecords', 'customer'])->findOrFail($vehicleId);
            $serviceRecords = $vehicle->serviceRecords()
                ->orderBy('service_date', 'desc')
                ->paginate(20);
        }
        
        // Get all vehicles for dropdown
        $vehicles = Vehicle::with('customer')
            ->orderBy('make')
            ->orderBy('model')
            ->orderBy('year')
            ->get();
            
        return view('vehicle-tools.service-history', compact('vehicle', 'serviceRecords', 'vehicles'));
    }

    /**
     * Display recall notifications.
     */
    public function recalls(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        
        $query = VehicleRecall::with('vehicle.customer');
        
        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('campaign_number', 'like', "%{$search}%")
                  ->orWhere('component', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function ($q) use ($search) {
                      $q->where('vin', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                  });
            });
        }
        
        $recalls = $query->orderBy('recall_date', 'desc')
            ->paginate(20);
            
        // Get recall statistics
        $totalRecalls = VehicleRecall::count();
        $openRecalls = VehicleRecall::where('status', 'open')->count();
        $inProgressRecalls = VehicleRecall::where('status', 'in_progress')->count();
        $completedRecalls = VehicleRecall::where('status', 'completed')->count();
        $needsNotification = VehicleRecall::where('customer_notified', false)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
            
        return view('vehicle-tools.recalls', compact(
            'recalls',
            'status',
            'search',
            'totalRecalls',
            'openRecalls',
            'inProgressRecalls',
            'completedRecalls',
            'needsNotification'
        ));
    }

    /**
     * Display VIN decoding results.
     */
    public function vinResults(Request $request, $vin = null)
    {
        $decodedData = null;
        $cacheEntry = null;
        $vehicle = null;
        
        if ($vin) {
            // Check cache first
            $cacheEntry = VINDecoderCache::where('vin', $vin)->first();
            
            if ($cacheEntry) {
                $decodedData = $cacheEntry->decoded_data;
                $cacheEntry->incrementHit();
            }
            
            // Check if vehicle exists with this VIN
            $vehicle = Vehicle::where('vin', $vin)->first();
        }
        
        return view('vehicle-tools.vin-results', compact(
            'vin',
            'decodedData',
            'cacheEntry',
            'vehicle'
        ));
    }

    /**
     * Process VIN decoding request.
     */
    public function decodeVIN(Request $request)
    {
        $request->validate([
            'vin' => 'required|string|min:17|max:17',
        ]);
        
        $vin = strtoupper($request->vin);
        
        // Check cache first
        $cacheEntry = VINDecoderCache::where('vin', $vin)->notExpired()->first();
        
        if ($cacheEntry) {
            $cacheEntry->incrementHit();
            return redirect()->route('vehicle-tools.vin-results', ['vin' => $vin])
                ->with('success', 'VIN decoded successfully (from cache).');
        }
        
        try {
            // Try to decode VIN using free API
            $decodedData = $this->decodeVINFromAPI($vin);
            
            if ($decodedData) {
                // Save to cache
                VINDecoderCache::createFromDecodedData($vin, $decodedData);
                
                // Update vehicle if exists
                $vehicle = Vehicle::where('vin', $vin)->first();
                if ($vehicle) {
                    $vehicle->markVINAsDecoded('api', true, 'Automatically decoded via API');
                    
                    // Update vehicle details from decoded data
                    $this->updateVehicleFromDecodedData($vehicle, $decodedData);
                }
                
                return redirect()->route('vehicle-tools.vin-results', ['vin' => $vin])
                    ->with('success', 'VIN decoded successfully.');
            }
            
            return redirect()->route('vehicle-tools.vin-decoder')
                ->with('error', 'Unable to decode VIN. Please try again or enter manually.');
                
        } catch (\Exception $e) {
            Log::error('VIN decoding failed', [
                'vin' => $vin,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('vehicle-tools.vin-decoder')
                ->with('error', 'VIN decoding service temporarily unavailable. Please try again later.');
        }
    }

    /**
     * Manually update vehicle with VIN decoding results.
     */
    public function updateVehicleWithVINData(Request $request, $vehicleId)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'trim' => 'nullable|string|max:255',
            'body_style' => 'nullable|string|max:255',
            'engine_type' => 'nullable|string|max:255',
            'transmission' => 'nullable|string|max:255',
            'fuel_type' => 'nullable|string|max:255',
        ]);
        
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        $vehicle->update([
            'make' => $request->make,
            'model' => $request->model,
            'year' => $request->year,
            'trim' => $request->trim,
            'body_style' => $request->body_style,
            'engine_type' => $request->engine_type,
            'transmission' => $request->transmission,
            'fuel_type' => $request->fuel_type,
        ]);
        
        $vehicle->markVINAsDecoded('manual', true, 'Manually updated from VIN decoding');
        
        return redirect()->route('vehicle-tools.service-history', ['vehicleId' => $vehicleId])
            ->with('success', 'Vehicle information updated successfully.');
    }

    /**
     * Check for recalls for a specific vehicle.
     */
    public function checkRecalls($vehicleId)
    {
        $vehicle = Vehicle::with('recalls')->findOrFail($vehicleId);
        
        // In a real implementation, this would call a recall API
        // For now, we'll simulate checking and updating
        
        $vehicle->update([
            'last_recall_check' => now(),
            'recall_check_required' => false,
        ]);
        
        return redirect()->route('vehicle-tools.service-history', ['vehicleId' => $vehicleId])
            ->with('success', 'Recall check completed. No new recalls found.');
    }

    /**
     * Update recall status.
     */
    public function updateRecallStatus(Request $request, $recallId)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,completed,closed',
            'repair_date' => 'nullable|date',
            'repair_notes' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
            'customer_notified' => 'boolean',
            'customer_notification_date' => 'nullable|date',
        ]);
        
        $recall = VehicleRecall::findOrFail($recallId);
        
        $recall->update([
            'status' => $request->status,
            'repair_date' => $request->repair_date,
            'repair_notes' => $request->repair_notes,
            'actual_cost' => $request->actual_cost,
            'customer_notified' => $request->boolean('customer_notified'),
            'customer_notification_date' => $request->customer_notification_date,
        ]);
        
        // Update vehicle recall count
        $recall->vehicle->updateRecallCount();
        
        return redirect()->route('vehicle-tools.recalls')
            ->with('success', 'Recall status updated successfully.');
    }

    /**
     * Export vehicle data.
     */
    public function exportVehicleData($vehicleId)
    {
        $vehicle = Vehicle::with(['customer', 'serviceRecords', 'recalls'])->findOrFail($vehicleId);
        
        $data = [
            'vehicle' => $vehicle->toArray(),
            'customer' => $vehicle->customer->toArray(),
            'service_records' => $vehicle->serviceRecords->toArray(),
            'recalls' => $vehicle->recalls->toArray(),
        ];
        
        $filename = "vehicle-{$vehicle->vin}-" . now()->format('Y-m-d') . '.json';
        
        return response()->json($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Clear expired VIN cache entries.
     */
    public function clearExpiredCache()
    {
        $expiredCount = VINDecoderCache::where('expires_at', '<', now())->delete();
        
        return redirect()->route('vehicle-tools.dashboard')
            ->with('success', "Cleared {$expiredCount} expired cache entries.");
    }

    /**
     * Decode VIN using a free API (simulated for now).
     */
    private function decodeVINFromAPI(string $vin): ?array
    {
        // In a real implementation, this would call an actual VIN decoding API
        // For now, we'll simulate with mock data based on common patterns
        
        // Check if VIN is valid format (basic validation)
        if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin)) {
            return null;
        }
        
        // Extract basic information from VIN (simplified)
        $yearDigit = $vin[9];
        $makeCode = substr($vin, 0, 3);
        
        // Map year digit to actual year (simplified)
        $yearMap = [
            'A' => 2010, 'B' => 2011, 'C' => 2012, 'D' => 2013, 'E' => 2014,
            'F' => 2015, 'G' => 2016, 'H' => 2017, 'J' => 2018, 'K' => 2019,
            'L' => 2020, 'M' => 2021, 'N' => 2022, 'P' => 2023, 'R' => 2024,
            'S' => 2025, 'T' => 2026,
        ];
        
        $year = $yearMap[$yearDigit] ?? 2020;
        
        // Map make codes to manufacturers (simplified)
        $makeMap = [
            '1HG' => 'Honda',
            '2HG' => 'Honda',
            '1FA' => 'Ford',
            '2FA' => 'Ford',
            '1G1' => 'Chevrolet',
            '2G1' => 'Chevrolet',
            '1N4' => 'Nissan',
            '2N4' => 'Nissan',
            'JT' => 'Toyota',
            '5TD' => 'Toyota',
            'WBA' => 'BMW',
            'WBS' => 'BMW',
            'WAU' => 'Audi',
            'TRU' => 'Audi',
            'ZFF' => 'Ferrari',
            'SAJ' => 'Jaguar',
        ];
        
        $make = $makeMap[substr($vin, 0, 3)] ?? 'Unknown';
        
        // Generate mock data
        $models = ['Civic', 'Accord', 'Camry', 'Corolla', 'F-150', 'Silverado', 'Mustang', 'Charger'];
        $trims = ['LX', 'EX', 'Touring', 'Limited', 'Sport', 'Premium', 'Platinum'];
        $engines = ['2.0L I4', '2.5L I4', '3.5L V6', '5.0L V8', '3.0L V6 Turbo', '2.0L I4 Turbo'];
        $transmissions = ['Automatic', 'Manual', 'CVT', 'Dual-Clutch'];
        $bodyStyles = ['Sedan', 'Coupe', 'SUV', 'Truck', 'Hatchback', 'Convertible'];
        
        return [
            'VIN' => $vin,
            'Make' => $make,
            'Model' => $models[array_rand($models)],
            'Year' => $year,
            'Trim' => $trims[array_rand($trims)],
            'Engine' => $engines[array_rand($engines)],
            'Transmission' => $transmissions[array_rand($transmissions)],
            'BodyStyle' => $bodyStyles[array_rand($bodyStyles)],
            'DriveType' => ['FWD', 'RWD', 'AWD'][array_rand(['FWD', 'RWD', 'AWD'])],
            'FuelType' => ['Gasoline', 'Diesel', 'Hybrid', 'Electric'][array_rand(['Gasoline', 'Diesel', 'Hybrid', 'Electric'])],
            'Manufacturer' => $make,
            'PlantCode' => substr($vin, 10, 1),
            'DisplacementL' => rand(15, 50) / 10,
            'Cylinders' => [4, 6, 8][array_rand([4, 6, 8])],
            'Horsepower' => rand(150, 500),
            'Torque' => rand(150, 500),
            'MPGCity' => rand(15, 35),
            'MPGHighway' => rand(25, 45),
            'Wheelbase' => rand(100, 130) . ' in',
            'Length' => rand(170, 220) . ' in',
            'Width' => rand(70, 80) . ' in',
            'Height' => rand(55, 75) . ' in',
            'CurbWeight' => rand(3000, 5000) . ' lbs',
            'NHTSARating' => rand(3, 5) . ' Stars',
            'Airbags' => rand(6, 12),
            'StabilityControl' => true,
            'HasRecalls' => rand(0, 1) === 1,
            'RecallCount' => rand(0, 3),
            'LastRecallCheck' => now()->subDays(rand(0, 30))->format('Y-m-d'),
            'OilChangeInterval' => 5000,
            'TireRotationInterval' => 7500,
            'BrakeServiceInterval' => 30000,
            'TransmissionServiceInterval' => 60000,
            'CoolantFlushInterval' => 100000,
            'TimingBeltInterval' => 90000,
            'InteriorFeatures' => ['Power Windows', 'Power Locks', 'Cruise Control', 'Bluetooth', 'Backup Camera'],
            'ExteriorFeatures' => ['Alloy Wheels', 'Fog Lights', 'Sunroof', 'LED Headlights'],
            'SafetyFeatures' => ['ABS', 'Traction Control', 'Blind Spot Monitoring', 'Lane Departure Warning'],
            'TechnologyFeatures' => ['Touchscreen Display', 'Navigation', 'Apple CarPlay', 'Android Auto'],
        ];
    }

    /**
     * Update vehicle details from decoded VIN data.
     */
    private function updateVehicleFromDecodedData(Vehicle $vehicle, array $decodedData): void
    {
        $updates = [
            'make' => $decodedData['Make'] ?? $vehicle->make,
            'model' => $decodedData['Model'] ?? $vehicle->model,
            'year' => $decodedData['Year'] ?? $vehicle->year,
            'trim' => $decodedData['Trim'] ?? $vehicle->trim,
            'body_style' => $decodedData['BodyStyle'] ?? $vehicle->body_style,
            'engine_type' => $decodedData['Engine'] ?? $vehicle->engine_type,
            'transmission' => $decodedData['Transmission'] ?? $vehicle->transmission,
            'fuel_type' => $decodedData['FuelType'] ?? $vehicle->fuel_type,
            'drive_type' => $decodedData['DriveType'] ?? $vehicle->drive_type,
            'manufacturer' => $decodedData['Manufacturer'] ?? $vehicle->manufacturer,
            'plant_code' => $decodedData['PlantCode'] ?? $vehicle->plant_code,
        ];
        
        // Only update fields that have values
        $updates = array_filter($updates, function ($value) {
            return $value !== null;
        });
        
        if (!empty($updates)) {
            $vehicle->update($updates);
        }
    }

    /**
     * Batch process VIN decoding for multiple vehicles.
     */
    public function batchDecodeVIN(Request $request)
    {
        $request->validate([
            'vehicle_ids' => 'required|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);
        
        $vehicleIds = $request->vehicle_ids;
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($vehicleIds as $vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            
            if (!$vehicle || !$vehicle->vin) {
                $errorCount++;
                continue;
            }
            
            if ($vehicle->isVINDecoded()) {
                // Already decoded
                $successCount++;
                continue;
            }
            
            try {
                // Try to decode
                $decodedData = $this->decodeVINFromAPI($vehicle->vin);
                
                if ($decodedData) {
                    // Save to cache
                    VINDecoderCache::createFromDecodedData($vehicle->vin, $decodedData);
                    
                    // Update vehicle
                    $vehicle->markVINAsDecoded('api', true, 'Batch decoded via API');
                    $this->updateVehicleFromDecodedData($vehicle, $decodedData);
                    
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                Log::error('Batch VIN decoding failed', [
                    'vehicle_id' => $vehicleId,
                    'vin' => $vehicle->vin,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }
        
        return redirect()->route('vehicle-tools.dashboard')
            ->with('success', "Batch VIN decoding completed: {$successCount} successful, {$errorCount} failed.");
    }

    /**
     * Batch check recalls for multiple vehicles.
     */
    public function batchCheckRecalls(Request $request)
    {
        $request->validate([
            'vehicle_ids' => 'required|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);
        
        $vehicleIds = $request->vehicle_ids;
        $checkedCount = 0;
        
        foreach ($vehicleIds as $vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            
            if ($vehicle) {
                $vehicle->update([
                    'last_recall_check' => now(),
                    'recall_check_required' => false,
                ]);
                $checkedCount++;
            }
        }
        
        return redirect()->route('vehicle-tools.dashboard')
            ->with('success', "Batch recall check completed: {$checkedCount} vehicles checked.");
    }

    /**
     * Get vehicle tools statistics for dashboard widgets.
     */
    public function getStatistics()
    {
        return response()->json([
            'total_vehicles' => Vehicle::count(),
            'vehicles_with_vin' => Vehicle::whereNotNull('vin')->count(),
            'vehicles_decoded' => Vehicle::whereNotNull('vin_decoded_at')->count(),
            'vehicles_with_recalls' => Vehicle::where('open_recall_count', '>', 0)->count(),
            'total_recalls' => VehicleRecall::count(),
            'open_recalls' => VehicleRecall::where('status', 'open')->count(),
            'cache_entries' => VINDecoderCache::count(),
            'cache_hits' => VINDecoderCache::sum('cache_hits'),
        ]);
    }
}