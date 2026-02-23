<?php

namespace Database\Seeders;

use App\Models\InventoryCategory;
use App\Models\InventorySupplier;
use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('inventory')->delete();
        DB::table('inventory_categories')->delete();
        DB::table('inventory_suppliers')->delete();
        
        // Create categories
        $categories = [
            ['name' => 'Engine Parts', 'slug' => 'engine-parts', 'color' => '#dc3545', 'icon' => 'fas fa-cogs'],
            ['name' => 'Brake System', 'slug' => 'brake-system', 'color' => '#fd7e14', 'icon' => 'fas fa-stop-circle'],
            ['name' => 'Suspension', 'slug' => 'suspension', 'color' => '#ffc107', 'icon' => 'fas fa-car-side'],
            ['name' => 'Electrical', 'slug' => 'electrical', 'color' => '#28a745', 'icon' => 'fas fa-bolt'],
            ['name' => 'Filters', 'slug' => 'filters', 'color' => '#20c997', 'icon' => 'fas fa-filter'],
            ['name' => 'Fluids', 'slug' => 'fluids', 'color' => '#17a2b8', 'icon' => 'fas fa-oil-can'],
            ['name' => 'Belts & Hoses', 'slug' => 'belts-hoses', 'color' => '#007bff', 'icon' => 'fas fa-link'],
            ['name' => 'Exhaust', 'slug' => 'exhaust', 'color' => '#6f42c1', 'icon' => 'fas fa-smog'],
            ['name' => 'Cooling System', 'slug' => 'cooling-system', 'color' => '#e83e8c', 'icon' => 'fas fa-temperature-low'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'color' => '#6c757d', 'icon' => 'fas fa-tools'],
        ];
        
        foreach ($categories as $category) {
            InventoryCategory::create($category);
        }
        
        // Create suppliers
        $suppliers = [
            [
                'name' => 'AutoParts Direct',
                'code' => 'APD001',
                'contact_name' => 'John Supplier',
                'contact_email' => 'john@autopartsdirect.com',
                'contact_phone' => '(555) 123-4567',
                'website' => 'https://autopartsdirect.com',
                'address' => '123 Supplier St',
                'city' => 'Detroit',
                'state' => 'MI',
                'zip_code' => '48201',
                'country' => 'USA',
                'payment_terms' => 'Net 30',
                'credit_limit' => 50000.00,
                'discount_percentage' => 5.00,
                'is_preferred' => true,
                'lead_time_days' => 2,
            ],
            [
                'name' => 'OEM Parts Co',
                'code' => 'OEM002',
                'contact_name' => 'Sarah Manufacturer',
                'contact_email' => 'sarah@oemparts.com',
                'contact_phone' => '(555) 987-6543',
                'website' => 'https://oemparts.com',
                'address' => '456 Manufacturer Ave',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip_code' => '60601',
                'country' => 'USA',
                'payment_terms' => 'Net 45',
                'credit_limit' => 75000.00,
                'discount_percentage' => 3.00,
                'is_preferred' => true,
                'lead_time_days' => 3,
            ],
            [
                'name' => 'Budget Auto Supply',
                'code' => 'BAS003',
                'contact_name' => 'Mike Wholesaler',
                'contact_email' => 'mike@budgetauto.com',
                'contact_phone' => '(555) 456-7890',
                'website' => 'https://budgetauto.com',
                'address' => '789 Wholesale Blvd',
                'city' => 'Dallas',
                'state' => 'TX',
                'zip_code' => '75201',
                'country' => 'USA',
                'payment_terms' => 'Net 15',
                'credit_limit' => 25000.00,
                'discount_percentage' => 2.00,
                'is_preferred' => false,
                'lead_time_days' => 5,
            ],
        ];
        
        foreach ($suppliers as $supplier) {
            InventorySupplier::create($supplier);
        }
        
        // Create inventory items
        $inventoryItems = [
            // Engine Parts
            [
                'part_number' => 'ENG-001',
                'name' => 'Oil Filter',
                'description' => 'High-quality synthetic oil filter for most vehicles',
                'category_id' => InventoryCategory::where('slug', 'engine-parts')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'APD001')->first()->id,
                'manufacturer' => 'Fram',
                'oem_number' => 'PH3614',
                'upc' => '012345678901',
                'location' => 'Shelf A',
                'bin' => 'B3',
                'quantity' => 45,
                'minimum_stock' => 10,
                'reorder_point' => 20,
                'cost_price' => 8.50,
                'retail_price' => 19.99,
                'wholesale_price' => 15.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            [
                'part_number' => 'ENG-002',
                'name' => 'Spark Plugs (Set of 4)',
                'description' => 'Iridium spark plugs for improved performance',
                'category_id' => InventoryCategory::where('slug', 'engine-parts')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'OEM002')->first()->id,
                'manufacturer' => 'NGK',
                'oem_number' => 'ILTR5A-13G',
                'upc' => '012345678902',
                'location' => 'Shelf B',
                'bin' => 'B1',
                'quantity' => 32,
                'minimum_stock' => 8,
                'reorder_point' => 15,
                'cost_price' => 24.00,
                'retail_price' => 59.99,
                'wholesale_price' => 45.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Brake System
            [
                'part_number' => 'BRK-001',
                'name' => 'Brake Pads (Front)',
                'description' => 'Ceramic brake pads for quiet operation',
                'category_id' => InventoryCategory::where('slug', 'brake-system')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'APD001')->first()->id,
                'manufacturer' => 'Bosch',
                'oem_number' => 'BC905',
                'upc' => '012345678903',
                'location' => 'Shelf C',
                'bin' => 'C2',
                'quantity' => 18,
                'minimum_stock' => 5,
                'reorder_point' => 10,
                'cost_price' => 35.00,
                'retail_price' => 89.99,
                'wholesale_price' => 65.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            [
                'part_number' => 'BRK-002',
                'name' => 'Brake Rotors (Pair)',
                'description' => 'Premium drilled and slotted rotors',
                'category_id' => InventoryCategory::where('slug', 'brake-system')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'OEM002')->first()->id,
                'manufacturer' => 'Brembo',
                'oem_number' => '09.8215.10',
                'upc' => '012345678904',
                'location' => 'Shelf C',
                'bin' => 'C3',
                'quantity' => 8,
                'minimum_stock' => 3,
                'reorder_point' => 6,
                'cost_price' => 120.00,
                'retail_price' => 299.99,
                'wholesale_price' => 220.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Suspension
            [
                'part_number' => 'SUS-001',
                'name' => 'Strut Assembly',
                'description' => 'Complete strut assembly with spring',
                'category_id' => InventoryCategory::where('slug', 'suspension')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'BAS003')->first()->id,
                'manufacturer' => 'Monroe',
                'oem_number' => '171649',
                'upc' => '012345678905',
                'location' => 'Shelf D',
                'bin' => 'D1',
                'quantity' => 6,
                'minimum_stock' => 2,
                'reorder_point' => 4,
                'cost_price' => 85.00,
                'retail_price' => 199.99,
                'wholesale_price' => 150.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Electrical
            [
                'part_number' => 'ELC-001',
                'name' => 'Car Battery',
                'description' => 'Maintenance-free AGM battery',
                'category_id' => InventoryCategory::where('slug', 'electrical')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'APD001')->first()->id,
                'manufacturer' => 'Optima',
                'oem_number' => '8022-091',
                'upc' => '012345678906',
                'location' => 'Floor',
                'bin' => 'F1',
                'quantity' => 12,
                'minimum_stock' => 4,
                'reorder_point' => 8,
                'cost_price' => 150.00,
                'retail_price' => 349.99,
                'wholesale_price' => 280.00,
                'core_price' => 25.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Filters
            [
                'part_number' => 'FIL-001',
                'name' => 'Air Filter',
                'description' => 'High-flow air filter',
                'category_id' => InventoryCategory::where('slug', 'filters')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'BAS003')->first()->id,
                'manufacturer' => 'K&N',
                'oem_number' => '33-2304',
                'upc' => '012345678907',
                'location' => 'Shelf A',
                'bin' => 'B2',
                'quantity' => 25,
                'minimum_stock' => 8,
                'reorder_point' => 15,
                'cost_price' => 35.00,
                'retail_price' => 79.99,
                'wholesale_price' => 60.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Fluids
            [
                'part_number' => 'FLU-001',
                'name' => 'Synthetic Oil 5W-30 (5qt)',
                'description' => 'Full synthetic motor oil',
                'category_id' => InventoryCategory::where('slug', 'fluids')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'APD001')->first()->id,
                'manufacturer' => 'Mobil 1',
                'oem_number' => '14977',
                'upc' => '012345678908',
                'location' => 'Shelf E',
                'bin' => 'E1',
                'quantity' => 60,
                'minimum_stock' => 20,
                'reorder_point' => 40,
                'cost_price' => 25.00,
                'retail_price' => 44.99,
                'wholesale_price' => 35.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Belts & Hoses
            [
                'part_number' => 'BLT-001',
                'name' => 'Serpentine Belt',
                'description' => 'Multi-rib serpentine belt',
                'category_id' => InventoryCategory::where('slug', 'belts-hoses')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'OEM002')->first()->id,
                'manufacturer' => 'Gates',
                'oem_number' => 'K060850',
                'upc' => '012345678909',
                'location' => 'Shelf F',
                'bin' => 'F2',
                'quantity' => 15,
                'minimum_stock' => 5,
                'reorder_point' => 10,
                'cost_price' => 18.00,
                'retail_price' => 39.99,
                'wholesale_price' => 30.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Low stock item
            [
                'part_number' => 'LOW-001',
                'name' => 'Wiper Blades (Pair)',
                'description' => 'All-season beam blade wiper blades',
                'category_id' => InventoryCategory::where('slug', 'accessories')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'BAS003')->first()->id,
                'manufacturer' => 'Bosch',
                'oem_number' => '3397007016',
                'upc' => '012345678910',
                'location' => 'Shelf G',
                'bin' => 'G1',
                'quantity' => 3,
                'minimum_stock' => 10,
                'reorder_point' => 20,
                'cost_price' => 12.00,
                'retail_price' => 29.99,
                'wholesale_price' => 22.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
            // Out of stock item
            [
                'part_number' => 'OUT-001',
                'name' => 'Catalytic Converter',
                'description' => 'EPA compliant catalytic converter',
                'category_id' => InventoryCategory::where('slug', 'exhaust')->first()->id,
                'supplier_id' => InventorySupplier::where('code', 'OEM002')->first()->id,
                'manufacturer' => 'Walker',
                'oem_number' => '15892',
                'upc' => '012345678911',
                'location' => 'Shelf H',
                'bin' => 'H1',
                'quantity' => 0,
                'minimum_stock' => 2,
                'reorder_point' => 4,
                'cost_price' => 280.00,
                'retail_price' => 699.99,
                'wholesale_price' => 550.00,
                'is_taxable' => true,
                'tax_rate' => 7.5,
                'is_active' => true,
            ],
        ];
        
        foreach ($inventoryItems as $item) {
            // Calculate status based on quantity
            $quantity = $item['quantity'];
            $reorderPoint = $item['reorder_point'];
            
            if ($quantity <= 0) {
                $status = 'out_of_stock';
            } elseif ($quantity <= $reorderPoint) {
                $status = 'low_stock';
            } else {
                $status = 'in_stock';
            }
            
            $item['status'] = $status;
            
            // Calculate profit margin
            $cost = $item['cost_price'];
            $retail = $item['retail_price'];
            $profitMargin = $cost > 0 ? (($retail - $cost) / $cost) * 100 : 0;
            $item['profit_margin'] = round($profitMargin, 2);
            
            Inventory::create($item);
        }
        
        $this->command->info('Inventory seeded successfully!');
        $this->command->info('Created: ' . count($categories) . ' categories');
        $this->command->info('Created: ' . count($suppliers) . ' suppliers');
        $this->command->info('Created: ' . count($inventoryItems) . ' inventory items');
    }
}