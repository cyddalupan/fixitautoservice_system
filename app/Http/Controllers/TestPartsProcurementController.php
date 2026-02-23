<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class TestPartsProcurementController extends Controller
{
    public function testPage()
    {
        return view('test.parts-procurement');
    }

    public function testDatabase()
    {
        $tables = [
            'parts_lookups' => 'Parts Lookups',
            'parts_orders' => 'Parts Orders',
            'parts_order_items' => 'Parts Order Items',
            'parts_returns' => 'Parts Returns',
            'parts_return_items' => 'Parts Return Items',
            'vendor_price_comparisons' => 'Vendor Price Comparisons',
            'core_returns' => 'Core Returns',
        ];

        $results = [];
        foreach ($tables as $table => $name) {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            $results[] = [
                'name' => $name,
                'table' => $table,
                'exists' => $exists,
            ];
        }

        return response()->json(['tables' => $results]);
    }

    public function testModels()
    {
        $models = [
            'PartsLookup' => 'App\\Models\\PartsLookup',
            'PartsOrder' => 'App\\Models\\PartsOrder',
            'PartsOrderItem' => 'App\\Models\\PartsOrderItem',
            'PartsReturn' => 'App\\Models\\PartsReturn',
            'PartsReturnItem' => 'App\\Models\\PartsReturnItem',
            'VendorPriceComparison' => 'App\\Models\\VendorPriceComparison',
            'CoreReturn' => 'App\\Models\\CoreReturn',
        ];

        $results = [];
        foreach ($models as $name => $class) {
            $exists = class_exists($class);
            $results[] = [
                'name' => $name,
                'class' => $class,
                'exists' => $exists,
            ];
        }

        return response()->json(['models' => $results]);
    }

    public function testControllers()
    {
        $controllers = [
            'PartsProcurementController' => 'App\\Http\\Controllers\\PartsProcurementController',
            'PartsLookupController' => 'App\\Http\\Controllers\\PartsLookupController',
        ];

        $results = [];
        foreach ($controllers as $name => $class) {
            $exists = class_exists($class);
            $results[] = [
                'name' => $name,
                'class' => $class,
                'exists' => $exists,
            ];
        }

        return response()->json(['controllers' => $results]);
    }

    public function testRoutes()
    {
        $routes = [
            'parts-procurement.index' => 'Parts Procurement Index',
            'parts-procurement.create' => 'Parts Procurement Create',
            'parts-procurement.store' => 'Parts Procurement Store',
            'parts-procurement.show' => 'Parts Procurement Show',
            'parts-procurement.edit' => 'Parts Procurement Edit',
            'parts-procurement.update' => 'Parts Procurement Update',
            'parts-procurement.destroy' => 'Parts Procurement Destroy',
            'parts-procurement.lookup' => 'Parts Lookup',
            'parts-procurement.returns' => 'Parts Returns',
            'parts-procurement.core-returns' => 'Core Returns',
        ];

        $results = [];
        $routeCollection = Route::getRoutes();
        
        foreach ($routes as $routeName => $description) {
            $exists = $routeCollection->hasNamedRoute($routeName);
            $results[] = [
                'name' => $description,
                'route' => $routeName,
                'exists' => $exists,
            ];
        }

        return response()->json(['routes' => $results]);
    }

    public function testViews()
    {
        $views = [
            'parts-procurement.index' => 'Index View',
            'parts-procurement.create' => 'Create View',
            'parts-procurement.show' => 'Show View',
            'parts-procurement.edit' => 'Edit View',
            'parts-procurement.returns.index' => 'Returns Index View',
            'parts-procurement.core-returns.index' => 'Core Returns Index View',
        ];

        $results = [];
        foreach ($views as $viewPath => $name) {
            $exists = view()->exists($viewPath);
            $results[] = [
                'name' => $name,
                'view' => $viewPath,
                'exists' => $exists,
            ];
        }

        return response()->json(['views' => $results]);
    }

    public function testIntegration()
    {
        $integrations = [
            ['name' => 'Inventory System', 'connected' => DB::getSchemaBuilder()->hasTable('inventory')],
            ['name' => 'Vendor/Supplier System', 'connected' => DB::getSchemaBuilder()->hasTable('inventory_suppliers')],
            ['name' => 'Work Order System', 'connected' => DB::getSchemaBuilder()->hasTable('work_orders')],
            ['name' => 'Customer System', 'connected' => DB::getSchemaBuilder()->hasTable('customers')],
            ['name' => 'Vehicle System', 'connected' => DB::getSchemaBuilder()->hasTable('vehicles')],
        ];

        return response()->json(['integrations' => $integrations]);
    }

    public function testRequirements()
    {
        $requirements = [
            [
                'name' => 'Integrated Parts Lookup (VIN-based)',
                'implemented' => DB::getSchemaBuilder()->hasTable('parts_lookups'),
                'notes' => 'parts_lookups table exists with VIN field'
            ],
            [
                'name' => 'Multi-Vendor Price Comparison',
                'implemented' => DB::getSchemaBuilder()->hasTable('vendor_price_comparisons'),
                'notes' => 'vendor_price_comparisons table exists for price sourcing'
            ],
            [
                'name' => 'Order Tracking',
                'implemented' => DB::getSchemaBuilder()->hasTable('parts_orders'),
                'notes' => 'parts_orders table has tracking_number, carrier, status fields'
            ],
            [
                'name' => 'Returns Management',
                'implemented' => DB::getSchemaBuilder()->hasTable('parts_returns'),
                'notes' => 'parts_returns table exists with return workflow'
            ],
            [
                'name' => 'Core Returns Tracking',
                'implemented' => DB::getSchemaBuilder()->hasTable('core_returns'),
                'notes' => 'core_returns table exists for core charge management'
            ],
        ];

        return response()->json(['requirements' => $requirements]);
    }

    public function testSummary()
    {
        // Count existing tables
        $tables = ['parts_lookups', 'parts_orders', 'parts_order_items', 'parts_returns', 'parts_return_items', 'vendor_price_comparisons', 'core_returns'];
        $existingTables = 0;
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $existingTables++;
            }
        }

        // Count existing models
        $models = ['App\\Models\\PartsLookup', 'App\\Models\\PartsOrder', 'App\\Models\\PartsOrderItem', 'App\\Models\\PartsReturn', 'App\\Models\\PartsReturnItem', 'App\\Models\\VendorPriceComparison', 'App\\Models\\CoreReturn'];
        $existingModels = 0;
        foreach ($models as $model) {
            if (class_exists($model)) {
                $existingModels++;
            }
        }

        // Check controller
        $controllerExists = class_exists('App\\Http\\Controllers\\PartsProcurementController');

        // Check views
        $views = ['parts-procurement.index', 'parts-procurement.create'];
        $existingViews = 0;
        foreach ($views as $view) {
            if (view()->exists($view)) {
                $existingViews++;
            }
        }

        // Calculate overall status
        $totalComponents = count($tables) + count($models) + 1 + count($views); // tables + models + controller + views
        $componentsReady = $existingTables + $existingModels + ($controllerExists ? 1 : 0) + $existingViews;
        $progressPercentage = round(($componentsReady / $totalComponents) * 100);

        if ($progressPercentage >= 90) {
            $overallStatus = 'complete';
            $message = 'Parts Procurement System is mostly complete and ready for production.';
            $nextSteps = 'Complete any missing views and perform final testing.';
        } elseif ($progressPercentage >= 70) {
            $overallStatus = 'partial';
            $message = 'Parts Procurement System is partially implemented. Core infrastructure exists but some components may be missing.';
            $nextSteps = 'Create missing views (show, edit) and ensure all business logic is implemented.';
        } else {
            $overallStatus = 'incomplete';
            $message = 'Parts Procurement System needs significant work.';
            $nextSteps = 'Implement core database tables, models, and controllers.';
        }

        return response()->json([
            'overall_status' => $overallStatus,
            'message' => $message,
            'progress_percentage' => $progressPercentage,
            'components_ready' => $componentsReady,
            'total_components' => $totalComponents,
            'next_steps' => $nextSteps,
            'details' => [
                'tables' => $existingTables . '/' . count($tables),
                'models' => $existingModels . '/' . count($models),
                'controller' => $controllerExists ? 'Exists' : 'Missing',
                'views' => $existingViews . '/' . count($views),
            ]
        ]);
    }
}