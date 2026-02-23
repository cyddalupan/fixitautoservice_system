<?php

namespace App\Http\Controllers;

use App\Models\PartsLookup;
use App\Models\Vehicle;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartsLookupController extends Controller
{
    /**
     * Display the parts lookup form
     */
    public function index()
    {
        return view('parts-procurement.lookup');
    }

    /**
     * Process parts lookup request
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'vin' => 'required|string|min:17|max:17',
            'part_category' => 'nullable|string',
            'part_number' => 'nullable|string',
        ]);

        $vin = strtoupper($request->vin);
        
        // First, try to find the vehicle
        $vehicle = Vehicle::where('vin', $vin)->first();
        
        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found in database. Please add vehicle first.');
        }

        // Get vehicle details for context
        $vehicleDetails = [
            'year' => $vehicle->year,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'trim' => $vehicle->trim,
            'engine' => $vehicle->engine,
        ];

        // Build query for parts lookup
        $query = PartsLookup::query();
        
        // Filter by vehicle attributes
        $query->where(function($q) use ($vehicleDetails) {
            $q->where('vehicle_year', $vehicleDetails['year'])
              ->orWhereNull('vehicle_year');
        });
        
        $query->where(function($q) use ($vehicleDetails) {
            $q->where('vehicle_make', $vehicleDetails['make'])
              ->orWhereNull('vehicle_make');
        });
        
        $query->where(function($q) use ($vehicleDetails) {
            $q->where('vehicle_model', $vehicleDetails['model'])
              ->orWhereNull('vehicle_model');
        });

        // Apply additional filters if provided
        if ($request->filled('part_category')) {
            $query->where('category', $request->part_category);
        }
        
        if ($request->filled('part_number')) {
            $query->where('part_number', 'like', '%' . $request->part_number . '%');
        }

        // Get matching parts
        $parts = $query->orderBy('category')->orderBy('part_name')->get();
        
        // Check inventory for each part
        $partsWithInventory = [];
        foreach ($parts as $part) {
            $inventoryItem = Inventory::where('part_number', $part->part_number)
                ->orWhere('name', 'like', '%' . $part->part_name . '%')
                ->first();
                
            $partsWithInventory[] = [
                'part' => $part,
                'in_stock' => $inventoryItem ? $inventoryItem->quantity : 0,
                'inventory_item' => $inventoryItem,
                'needs_order' => !$inventoryItem || $inventoryItem->quantity < 1,
            ];
        }

        // Get vendor price comparisons for parts that need ordering
        $partsNeedingOrder = array_filter($partsWithInventory, function($item) {
            return $item['needs_order'];
        });

        $vendorPrices = [];
        if (count($partsNeedingOrder) > 0) {
            $partNumbers = array_map(function($item) {
                return $item['part']->part_number;
            }, $partsNeedingOrder);
            
            // This would normally query a vendor price comparison table
            // For now, we'll simulate with some sample data
            $vendorPrices = $this->getSampleVendorPrices($partNumbers);
        }

        return view('parts-procurement.lookup-results', [
            'vehicle' => $vehicle,
            'vehicleDetails' => $vehicleDetails,
            'parts' => $partsWithInventory,
            'vendorPrices' => $vendorPrices,
            'vin' => $vin,
            'partCategory' => $request->part_category,
            'partNumber' => $request->part_number,
        ]);
    }

    /**
     * Get sample vendor prices (in real app, this would query vendor APIs)
     */
    private function getSampleVendorPrices($partNumbers)
    {
        $vendors = [
            ['id' => 1, 'name' => 'AutoParts Direct', 'delivery_days' => 2, 'rating' => 4.5],
            ['id' => 2, 'name' => 'CarParts Warehouse', 'delivery_days' => 1, 'rating' => 4.2],
            ['id' => 3, 'name' => 'OEM Parts Source', 'delivery_days' => 3, 'rating' => 4.7],
            ['id' => 4, 'name' => 'Local Supplier', 'delivery_days' => 0, 'rating' => 4.0],
        ];

        $prices = [];
        foreach ($partNumbers as $partNumber) {
            foreach ($vendors as $vendor) {
                // Generate realistic prices
                $basePrice = rand(50, 500);
                $shipping = $vendor['name'] === 'Local Supplier' ? 0 : rand(10, 50);
                $total = $basePrice + $shipping;
                
                $prices[$partNumber][] = [
                    'vendor_id' => $vendor['id'],
                    'vendor_name' => $vendor['name'],
                    'part_number' => $partNumber,
                    'price' => $basePrice,
                    'shipping' => $shipping,
                    'total' => $total,
                    'delivery_days' => $vendor['delivery_days'],
                    'rating' => $vendor['rating'],
                    'in_stock' => rand(0, 1) ? 'In Stock' : 'Backorder',
                ];
            }
            
            // Sort by total price
            usort($prices[$partNumber], function($a, $b) {
                return $a['total'] <=> $b['total'];
            });
        }

        return $prices;
    }

    /**
     * Search parts by keyword
     */
    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2',
        ]);

        $keyword = $request->keyword;
        
        $parts = PartsLookup::where('part_name', 'like', '%' . $keyword . '%')
            ->orWhere('part_number', 'like', '%' . $keyword . '%')
            ->orWhere('description', 'like', '%' . $keyword . '%')
            ->orWhere('category', 'like', '%' . $keyword . '%')
            ->orderBy('category')
            ->orderBy('part_name')
            ->limit(50)
            ->get();

        // Check inventory
        $partsWithInventory = [];
        foreach ($parts as $part) {
            $inventoryItem = Inventory::where('part_number', $part->part_number)
                ->orWhere('name', 'like', '%' . $part->part_name . '%')
                ->first();
                
            $partsWithInventory[] = [
                'part' => $part,
                'in_stock' => $inventoryItem ? $inventoryItem->quantity : 0,
                'inventory_item' => $inventoryItem,
            ];
        }

        return view('parts-procurement.search-results', [
            'keyword' => $keyword,
            'parts' => $partsWithInventory,
        ]);
    }

    /**
     * Get part details by ID
     */
    public function show($id)
    {
        $part = PartsLookup::findOrFail($id);
        
        // Check inventory
        $inventoryItem = Inventory::where('part_number', $part->part_number)
            ->orWhere('name', 'like', '%' . $part->part_name . '%')
            ->first();
            
        // Get vendor prices
        $vendorPrices = $this->getSampleVendorPrices([$part->part_number]);

        return view('parts-procurement.part-details', [
            'part' => $part,
            'inventory_item' => $inventoryItem,
            'vendor_prices' => $vendorPrices[$part->part_number] ?? [],
        ]);
    }

    /**
     * Get compatible vehicles for a part
     */
    public function compatibleVehicles($partId)
    {
        $part = PartsLookup::findOrFail($partId);
        
        // In a real app, this would query a compatibility table
        // For now, we'll return sample data
        $compatibleVehicles = [
            [
                'year' => $part->vehicle_year ?? '2020-2024',
                'make' => $part->vehicle_make ?? 'Multiple',
                'model' => $part->vehicle_model ?? 'Models',
                'notes' => 'Direct fit',
            ],
            [
                'year' => '2018-2022',
                'make' => $part->vehicle_make ?? 'Toyota',
                'model' => 'Camry, Corolla',
                'notes' => 'With adapter',
            ],
        ];

        return response()->json([
            'part' => $part,
            'compatible_vehicles' => $compatibleVehicles,
        ]);
    }

    /**
     * Get part categories for dropdown
     */
    public function categories()
    {
        $categories = PartsLookup::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        return response()->json($categories);
    }
}