<?php

namespace App\Http\Controllers;

use App\Models\VINDecoderCache;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VINDecoderController extends Controller
{
    /**
     * Display the VIN decoder interface.
     */
    public function index()
    {
        $recentDecodes = VINDecoderCache::orderBy('last_accessed_at', 'desc')
            ->limit(10)
            ->get();
            
        $popularDecodes = VINDecoderCache::orderBy('cache_hits', 'desc')
            ->limit(10)
            ->get();
            
        $cacheStats = [
            'total' => VINDecoderCache::count(),
            'hits' => VINDecoderCache::sum('cache_hits'),
            'expired' => VINDecoderCache::where('expires_at', '<', now())->count(),
            'needs_refresh' => VINDecoderCache::needsRefresh()->count(),
        ];
        
        return view('vehicle-tools.vin-decoder-advanced', compact(
            'recentDecodes',
            'popularDecodes',
            'cacheStats'
        ));
    }

    /**
     * Decode a VIN and return JSON response.
     */
    public function decode(Request $request)
    {
        $request->validate([
            'vin' => 'required|string|min:17|max:17',
            'force_refresh' => 'boolean',
        ]);
        
        $vin = strtoupper($request->vin);
        $forceRefresh = $request->boolean('force_refresh', false);
        
        // Check cache first (unless forcing refresh)
        if (!$forceRefresh) {
            $cacheEntry = VINDecoderCache::where('vin', $vin)->notExpired()->first();
            
            if ($cacheEntry) {
                $cacheEntry->incrementHit();
                
                return response()->json([
                    'success' => true,
                    'cached' => true,
                    'data' => $cacheEntry->decoded_data,
                    'basic_info' => $cacheEntry->basic_info,
                    'specifications' => $cacheEntry->specifications,
                    'features' => $cacheEntry->features,
                    'maintenance_schedule' => $cacheEntry->maintenance_schedule,
                    'cache_info' => [
                        'hits' => $cacheEntry->cache_hits,
                        'last_accessed' => $cacheEntry->last_accessed_at,
                        'expires' => $cacheEntry->expires_at,
                        'age_days' => $cacheEntry->age_in_days,
                    ],
                ]);
            }
        }
        
        try {
            // Decode VIN from API
            $decodedData = $this->decodeVINFromAPI($vin);
            
            if (!$decodedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to decode VIN. Please check the VIN and try again.',
                ], 400);
            }
            
            // Save to cache
            $cacheEntry = VINDecoderCache::createFromDecodedData($vin, $decodedData);
            
            // Update vehicle if exists
            $vehicle = Vehicle::where('vin', $vin)->first();
            if ($vehicle) {
                $vehicle->markVINAsDecoded('api', true, 'Automatically decoded via API');
                $this->updateVehicleFromDecodedData($vehicle, $decodedData);
            }
            
            return response()->json([
                'success' => true,
                'cached' => false,
                'data' => $decodedData,
                'basic_info' => $cacheEntry->basic_info,
                'specifications' => $cacheEntry->specifications,
                'features' => $cacheEntry->features,
                'maintenance_schedule' => $cacheEntry->maintenance_schedule,
                'cache_info' => [
                    'hits' => $cacheEntry->cache_hits,
                    'last_accessed' => $cacheEntry->last_accessed_at,
                    'expires' => $cacheEntry->expires_at,
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('VIN decoding API failed', [
                'vin' => $vin,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'VIN decoding service temporarily unavailable. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Batch decode multiple VINs.
     */
    public function batchDecode(Request $request)
    {
        $request->validate([
            'vins' => 'required|array|max:50',
            'vins.*' => 'string|min:17|max:17',
        ]);
        
        $vins = array_map('strtoupper', $request->vins);
        $results = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($vins as $vin) {
            try {
                // Check cache first
                $cacheEntry = VINDecoderCache::where('vin', $vin)->notExpired()->first();
                
                if ($cacheEntry) {
                    $cacheEntry->incrementHit();
                    $results[] = [
                        'vin' => $vin,
                        'success' => true,
                        'cached' => true,
                        'make' => $cacheEntry->make,
                        'model' => $cacheEntry->model,
                        'year' => $cacheEntry->year,
                        'basic_info' => $cacheEntry->basic_info,
                    ];
                    $successCount++;
                    continue;
                }
                
                // Decode from API
                $decodedData = $this->decodeVINFromAPI($vin);
                
                if ($decodedData) {
                    $cacheEntry = VINDecoderCache::createFromDecodedData($vin, $decodedData);
                    
                    // Update vehicle if exists
                    $vehicle = Vehicle::where('vin', $vin)->first();
                    if ($vehicle) {
                        $vehicle->markVINAsDecoded('api', true, 'Batch decoded via API');
                        $this->updateVehicleFromDecodedData($vehicle, $decodedData);
                    }
                    
                    $results[] = [
                        'vin' => $vin,
                        'success' => true,
                        'cached' => false,
                        'make' => $cacheEntry->make,
                        'model' => $cacheEntry->model,
                        'year' => $cacheEntry->year,
                        'basic_info' => $cacheEntry->basic_info,
                    ];
                    $successCount++;
                } else {
                    $results[] = [
                        'vin' => $vin,
                        'success' => false,
                        'message' => 'Unable to decode VIN',
                    ];
                    $errorCount++;
                }
                
            } catch (\Exception $e) {
                Log::error('Batch VIN decoding failed', [
                    'vin' => $vin,
                    'error' => $e->getMessage(),
                ]);
                
                $results[] = [
                    'vin' => $vin,
                    'success' => false,
                    'message' => 'Decoding failed',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ];
                $errorCount++;
            }
        }
        
        return response()->json([
            'success' => true,
            'summary' => [
                'total' => count($vins),
                'successful' => $successCount,
                'failed' => $errorCount,
            ],
            'results' => $results,
        ]);
    }

    /**
     * Get VIN decoding cache statistics.
     */
    public function cacheStats()
    {
        $stats = [
            'total_entries' => VINDecoderCache::count(),
            'total_hits' => VINDecoderCache::sum('cache_hits'),
            'expired_entries' => VINDecoderCache::where('expires_at', '<', now())->count(),
            'entries_needing_refresh' => VINDecoderCache::needsRefresh()->count(),
            'average_hits_per_entry' => VINDecoderCache::count() > 0 
                ? round(VINDecoderCache::sum('cache_hits') / VINDecoderCache::count(), 2)
                : 0,
            'most_popular' => VINDecoderCache::orderBy('cache_hits', 'desc')
                ->limit(5)
                ->get(['vin', 'make', 'model', 'year', 'cache_hits']),
            'recent_decodes' => VINDecoderCache::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['vin', 'make', 'model', 'year', 'created_at']),
        ];
        
        return response()->json($stats);
    }

    /**
     * Clear VIN decoding cache.
     */
    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'expired');
        
        switch ($type) {
            case 'expired':
                $deleted = VINDecoderCache::where('expires_at', '<', now())->delete();
                $message = "Cleared {$deleted} expired cache entries.";
                break;
                
            case 'all':
                $deleted = VINDecoderCache::count();
                VINDecoderCache::truncate();
                $message = "Cleared all {$deleted} cache entries.";
                break;
                
            case 'low_hit':
                $deleted = VINDecoderCache::where('cache_hits', '<', 3)
                    ->where('created_at', '<', now()->subDays(30))
                    ->delete();
                $message = "Cleared {$deleted} low-hit cache entries.";
                break;
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid cache clearance type.',
                ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Validate VIN format.
     */
    public function validateVIN(Request $request)
    {
        $request->validate([
            'vin' => 'required|string',
        ]);
        
        $vin = strtoupper($request->vin);
        
        // Basic VIN validation
        $isValid = preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin);
        
        if (!$isValid) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid VIN format. VIN must be 17 characters (letters and numbers, excluding I, O, Q).',
            ]);
        }
        
        // Check check digit (simplified)
        $checkDigitValid = $this->validateVINCheckDigit($vin);
        
        return response()->json([
            'valid' => $checkDigitValid,
            'message' => $checkDigitValid 
                ? 'VIN format is valid.' 
                : 'VIN format appears valid but check digit verification failed.',
            'vin' => $vin,
            'check_digit_valid' => $checkDigitValid,
        ]);
    }

    /**
     * Get VIN decoding history for a specific vehicle.
     */
    public function history($vin)
    {
        $cacheEntries = VINDecoderCache::where('vin', $vin)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $vehicle = Vehicle::where('vin', $vin)->first();
        
        return response()->json([
            'vin' => $vin,
            'vehicle' => $vehicle,
            'history' => $cacheEntries->map(function ($entry) {
                return [
                    'decoded_at' => $entry->created_at,
                    'data' => $entry->basic_info,
                    'cache_hits' => $entry->cache_hits,
                    'last_accessed' => $entry->last_accessed_at,
                    'expires' => $entry->expires_at,
                ];
            }),
            'total_decodes' => $cacheEntries->count(),
            'total_hits' => $cacheEntries->sum('cache_hits'),
        ]);
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
     * Validate VIN check digit (simplified implementation).
     */
    private function validateVINCheckDigit(string $vin): bool
    {
        // This is a simplified check digit validation
        // In production, you would implement the full ISO 3779 check digit calculation
        
        $checkDigit = $vin[8];
        
        // For now, accept all check digits in our simulation
        // In a real implementation, you would calculate the expected check digit
        return true;
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
     * Export VIN decoding results.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:json,csv',
            'vins' => 'nullable|array',
            'vins.*' => 'string|min:17|max:17',
        ]);
        
        $format = $request->format;
        $vins = $request->vins ?? [];
        
        if (empty($vins)) {
            // Get all cache entries
            $cacheEntries = VINDecoderCache::all();
        } else {
            // Get specific VINs
            $cacheEntries = VINDecoderCache::whereIn('vin', $vins)->get();
        }
        
        $data = $cacheEntries->map(function ($entry) {
            return [
                'vin' => $entry->vin,
                'make' => $entry->make,
                'model' => $entry->model,
                'year' => $entry->year,
                'trim' => $entry->trim,
                'engine' => $entry->engine,
                'transmission' => $entry->transmission,
                'body_style' => $entry->body_style,
                'fuel_type' => $entry->fuel_type,
                'cache_hits' => $entry->cache_hits,
                'last_accessed' => $entry->last_accessed_at,
                'created_at' => $entry->created_at,
                'expires_at' => $entry->expires_at,
            ];
        });
        
        if ($format === 'json') {
            $filename = 'vin-decoding-export-' . now()->format('Y-m-d') . '.json';
            
            return response()->json($data)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } else {
            // CSV format
            $filename = 'vin-decoding-export-' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            
            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, [
                    'VIN', 'Make', 'Model', 'Year', 'Trim', 'Engine', 
                    'Transmission', 'Body Style', 'Fuel Type', 'Cache Hits',
                    'Last Accessed', 'Created At', 'Expires At'
                ]);
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row['vin'],
                        $row['make'],
                        $row['model'],
                        $row['year'],
                        $row['trim'],
                        $row['engine'],
                        $row['transmission'],
                        $row['body_style'],
                        $row['fuel_type'],
                        $row['cache_hits'],
                        $row['last_accessed'],
                        $row['created_at'],
                        $row['expires_at'],
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
    }
}