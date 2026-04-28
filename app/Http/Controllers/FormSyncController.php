<?php

namespace App\Http\Controllers;

use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Models\VehicleColor;
use Illuminate\Http\Request;

class FormSyncController extends Controller
{
    /**
     * Sync all vehicle data (brands, models, colors) to JSON files
     * that the public forms can read without authentication.
     */
    public function syncToForm()
    {
        try {
            // 1. Collect brands (active only, sorted by name)
            $brands = VehicleBrand::where('is_active', true)
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->toArray();

            // 2. Collect models grouped by brand name
            $brandsWithModels = VehicleBrand::where('is_active', true)
                ->with(['activeModels' => function($q) {
                    $q->orderBy('name');
                }])
                ->orderBy('name')
                ->get();

            // Build the grouped models structure: { "Toyota": ["Vios", "Wigo", ...], "Honda": [...], ... }
            $modelsByBrand = [];
            foreach ($brandsWithModels as $brand) {
                $modelList = $brand->activeModels->pluck('name')->values()->toArray();
                if (!empty($modelList)) {
                    $modelsByBrand[$brand->name] = $modelList;
                }
            }

            // 3. Collect all active models as a flat list (for fallback)
            $allModels = VehicleModel::where('is_active', true)
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->toArray();

            // 4. Collect colors (active only, sorted by popularity then name)
            $colors = VehicleColor::where('is_active', true)
                ->orderBy('popularity_score', 'desc')
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->toArray();

            // Build the combined data
            $data = [
                'brands' => $brands,
                'models_by_brand' => $modelsByBrand,
                'all_models' => $allModels,
                'colors' => $colors,
                'counts' => [
                    'brands' => count($brands),
                    'models' => count($allModels),
                    'colors' => count($colors),
                ],
                'synced_at' => now()->toIso8601String(),
            ];

            // Save to JSON files in the shared storage path (public access)
            $formPath = '/var/www/fixit-form/public/vehicle-data';
            if (!is_dir($formPath)) {
                mkdir($formPath, 0755, true);
            }

            // Write brands
            file_put_contents(
                $formPath . '/brands.json',
                json_encode($brands, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // Write models grouped by brand
            file_put_contents(
                $formPath . '/models.json',
                json_encode($modelsByBrand, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // Write colors
            file_put_contents(
                $formPath . '/colors.json',
                json_encode($colors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            // Also write to the system app's public directory (for redundancy)
            $systemPath = '/var/www/fixit-system/public/vehicle-data';
            if (!is_dir($systemPath)) {
                mkdir($systemPath, 0755, true);
            }

            file_put_contents(
                $systemPath . '/brands.json',
                json_encode($brands, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            file_put_contents(
                $systemPath . '/models.json',
                json_encode($modelsByBrand, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            file_put_contents(
                $systemPath . '/colors.json',
                json_encode($colors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            return response()->json([
                'success' => true,
                'message' => 'Vehicle data synced successfully to forms!',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Serve the vehicle data as a public JSON endpoint.
     * This allows the forms to also fetch live data when needed.
     */
    public function serveVehicleData()
    {
        $brands = VehicleBrand::where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->toArray();

        $brandsWithModels = VehicleBrand::where('is_active', true)
            ->with(['activeModels' => function($q) {
                $q->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $modelsByBrand = [];
        foreach ($brandsWithModels as $brand) {
            $modelList = $brand->activeModels->pluck('name')->values()->toArray();
            if (!empty($modelList)) {
                $modelsByBrand[$brand->name] = $modelList;
            }
        }

        $allModels = VehicleModel::where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->toArray();

        $colors = VehicleColor::where('is_active', true)
            ->orderBy('popularity_score', 'desc')
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->toArray();

        return response()->json([
            'brands' => $brands,
            'models_by_brand' => $modelsByBrand,
            'all_models' => $allModels,
            'colors' => $colors,
            'counts' => [
                'brands' => count($brands),
                'models' => count($allModels),
                'colors' => count($colors),
            ],
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
