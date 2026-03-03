<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateInventorySampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-sample-data {--count=50 : Number of inventory items to create} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample data for inventory items linked to suppliers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $force = $this->option('force');

        // Check if there's existing data
        $existingCount = DB::table('inventory')->count();
        
        if ($existingCount > 0 && !$force) {
            if (!$this->confirm("There are already {$existingCount} inventory items in the database. Do you want to delete them and create new sample data?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
            
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('inventory')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->info("Deleted {$existingCount} existing inventory items.");
        }

        // Get all suppliers and categories
        $suppliers = DB::table('inventory_suppliers')->where('is_active', 1)->get();
        $categories = DB::table('inventory_categories')->get();

        if ($suppliers->isEmpty()) {
            $this->error('No active suppliers found. Please run suppliers:generate-sample-data first.');
            return 1;
        }

        if ($categories->isEmpty()) {
            $this->error('No categories found. Please create some categories first.');
            return 1;
        }

        $this->info("Generating {$count} sample inventory items...");
        $this->info("Using {$suppliers->count()} active suppliers and {$categories->count()} categories.");

        $inventoryItems = $this->generateInventoryItems($count, $suppliers, $categories);

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        foreach ($inventoryItems as $item) {
            DB::table('inventory')->insert($item);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Update supplier statistics
        $this->updateSupplierStatistics();

        $this->info("✅ Successfully created {$count} sample inventory items!");
        $this->info("📊 Statistics:");
        $this->info("   • Total inventory items: " . DB::table('inventory')->count());
        $this->info("   • Active items: " . DB::table('inventory')->where('is_active', 1)->count());
        $this->info("   • Total inventory value: ₱" . number_format(DB::table('inventory')->sum(DB::raw('quantity * cost_price')), 2));
        $this->info("   • Items per supplier: " . number_format($count / $suppliers->count(), 1));
        
        $this->newLine();
        $this->info("🔗 You can view the inventory at: https://app.fixitautoservices.com/inventory");
        $this->info("🔗 You can view the suppliers at: https://app.fixitautoservices.com/suppliers-management");

        return 0;
    }

    /**
     * Generate sample inventory items
     */
    private function generateInventoryItems(int $count, $suppliers, $categories): array
    {
        $items = [];
        $now = now();

        // Common automotive parts
        $partNames = [
            'Brake Pads',
            'Brake Rotors',
            'Brake Calipers',
            'Brake Fluid',
            'Engine Oil',
            'Oil Filter',
            'Air Filter',
            'Fuel Filter',
            'Spark Plugs',
            'Ignition Coils',
            'Battery',
            'Alternator',
            'Starter Motor',
            'Radiator',
            'Water Pump',
            'Thermostat',
            'Timing Belt',
            'Serpentine Belt',
            'Tensioner Pulley',
            'Shock Absorbers',
            'Struts',
            'Control Arms',
            'Ball Joints',
            'Tie Rod Ends',
            'CV Axles',
            'Wheel Bearings',
            'Wheel Hubs',
            'Tires',
            'Wheels',
            'Headlights',
            'Taillights',
            'Turn Signals',
            'Fog Lights',
            'Windshield Wipers',
            'Windshield',
            'Side Mirrors',
            'Bumpers',
            'Fenders',
            'Hood',
            'Doors',
            'Seats',
            'Steering Wheel',
            'Dashboard',
            'Radio',
            'Speakers',
            'AC Compressor',
            'AC Condenser',
            'AC Evaporator',
            'AC Refrigerant',
            'Heater Core',
        ];

        // Manufacturers
        $manufacturers = [
            'Bosch', 'Denso', 'NGK', 'ACDelco', 'Motorcraft', 'Mopar', 'Genuine', 'OEM', 
            'Brembo', 'ATE', 'TRW', 'Monroe', 'KYB', 'Bilstein', 'Koni',
            'Michelin', 'Bridgestone', 'Goodyear', 'Continental', 'Yokohama',
            'Philips', 'Osram', 'HELLA', 'Valeo', 'Magneti Marelli',
            'Gates', 'Dayco', 'Contitech', 'Bando',
            'Exide', 'Optima', 'Interstate', 'DieHard',
            'Mobil 1', 'Castrol', 'Valvoline', 'Shell', 'Pennzoil'
        ];

        // Vehicle makes
        $vehicleMakes = ['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Ford', 'Chevrolet', 'Hyundai', 'Kia', 'Isuzu', 'Mazda', 'Subaru', 'Suzuki'];

        // Vehicle models
        $vehicleModels = [
            'Vios', 'Corolla', 'Camry', 'Fortuner', 'Innova', 'Hilux',
            'City', 'Civic', 'Accord', 'CR-V', 'HR-V', 'BR-V',
            'Mirage', 'Lancer', 'Montero Sport', 'Strada', 'Outlander',
            'Almera', 'Sentra', 'Navara', 'Terra', 'Urvan',
            'Ranger', 'Everest', 'Escape', 'Explorer',
            'Trailblazer', 'Colorado', 'Captiva',
            'Accent', 'Elantra', 'Santa Fe', 'Tucson',
            'Sportage', 'Sorento', 'Picanto', 'Rio',
            'D-Max', 'MU-X', 'Crosswind',
            '3', '6', 'CX-5', 'CX-9',
            'Forester', 'Outback', 'XV',
            'Celerio', 'Swift', 'Ertiga', 'Vitara'
        ];

        // Status options
        $statuses = ['in_stock', 'low_stock', 'out_of_stock', 'discontinued', 'on_order'];

        for ($i = 0; $i < $count; $i++) {
            $partName = $partNames[$i % count($partNames)];
            $vehicleMake = $vehicleMakes[array_rand($vehicleMakes)];
            $vehicleModel = $vehicleModels[array_rand($vehicleModels)];
            $manufacturer = $manufacturers[array_rand($manufacturers)];
            
            $fullName = "{$manufacturer} {$partName} for {$vehicleMake} {$vehicleModel}";
            $partNumber = strtoupper(substr($manufacturer, 0, 3)) . '-' . 
                         strtoupper(substr($partName, 0, 3)) . '-' . 
                         strtoupper(substr($vehicleMake, 0, 3)) . '-' . 
                         str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            
            $supplier = $suppliers->random();
            $category = $categories->random();
            
            $costPrice = mt_rand(500, 50000);
            $retailPrice = $costPrice * (1 + mt_rand(20, 80) / 100); // 20-80% markup
            $wholesalePrice = $costPrice * (1 + mt_rand(10, 30) / 100); // 10-30% markup
            $quantity = mt_rand(0, 100);
            
            $status = $quantity > 20 ? 'in_stock' : 
                     ($quantity > 5 ? 'low_stock' : 
                     ($quantity > 0 ? 'out_of_stock' : 'on_order'));
            
            $items[] = [
                'part_number' => $partNumber,
                'name' => $fullName,
                'description' => $this->generateDescription($partName, $vehicleMake, $vehicleModel, $manufacturer),
                'category_id' => $category->id,
                'supplier_id' => $supplier->id,
                'manufacturer' => $manufacturer,
                'oem_number' => 'OEM-' . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'upc' => str_pad(mt_rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                'location' => 'Aisle ' . chr(65 + mt_rand(0, 5)) . ', Shelf ' . mt_rand(1, 10),
                'bin' => 'BIN-' . str_pad(mt_rand(1, 100), 3, '0', STR_PAD_LEFT),
                'quantity' => $quantity,
                'minimum_stock' => mt_rand(5, 20),
                'reorder_point' => mt_rand(10, 30),
                'cost_price' => $costPrice,
                'retail_price' => $retailPrice,
                'wholesale_price' => $wholesalePrice,
                'core_price' => mt_rand(0, 1) ? $costPrice * 0.3 : null,
                'is_taxable' => mt_rand(0, 1),
                'tax_rate' => mt_rand(0, 1) ? 12.00 : 0.00,
                'is_active' => mt_rand(0, 10) > 1 ? 1 : 0, // 90% active
                'status' => $status,
                'last_purchased' => $now->subDays(mt_rand(0, 90))->format('Y-m-d'),
                'last_sold' => $quantity > 0 ? $now->subDays(mt_rand(0, 30))->format('Y-m-d') : null,
                'total_sold' => mt_rand(0, 100),
                'total_sales' => mt_rand(0, 100) * $retailPrice,
                'total_cost' => mt_rand(0, 100) * $costPrice,
                'profit_margin' => (($retailPrice - $costPrice) / $costPrice) * 100,
                'turnover_rate' => mt_rand(0, 12),
                'notes' => $this->generateInventoryNotes($partName, $supplier->name),
                'image_url' => $this->generateImageUrl($partName),
                'barcode' => $this->generateBarcode($partNumber),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $items;
    }

    /**
     * Generate description for inventory item
     */
    private function generateDescription(string $partName, string $vehicleMake, string $vehicleModel, string $manufacturer): string
    {
        $descriptions = [
            "High-quality {$partName} for {$vehicleMake} {$vehicleModel}. Manufactured by {$manufacturer} to meet or exceed OEM specifications.",
            "Premium {$partName} designed specifically for {$vehicleMake} {$vehicleModel}. {$manufacturer} brand ensures reliability and performance.",
            "Genuine replacement {$partName} for {$vehicleMake} {$vehicleModel}. Made by {$manufacturer} with strict quality control standards.",
            "Aftermarket {$partName} compatible with {$vehicleMake} {$vehicleModel}. {$manufacturer} provides excellent value and performance.",
            "Performance-grade {$partName} for enhanced driving experience in {$vehicleMake} {$vehicleModel}. Manufactured by {$manufacturer}.",
            "OE-equivalent {$partName} for {$vehicleMake} {$vehicleModel}. {$manufacturer} ensures perfect fit and function.",
            "Durable {$partName} built to withstand Philippine road conditions. Compatible with {$vehicleMake} {$vehicleModel}.",
            "Cost-effective {$partName} solution for {$vehicleMake} {$vehicleModel}. {$manufacturer} quality at competitive pricing.",
        ];
        
        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate notes for inventory item
     */
    private function generateInventoryNotes(string $partName, string $supplierName): string
    {
        $notes = [
            "Fast-moving item. Reorder when stock reaches 10 units.",
            "Popular among customers. Keep good stock levels.",
            "Special order item. Lead time 7-14 days.",
            "Comes with 1-year warranty from {$supplierName}.",
            "Requires special handling. Store in dry area.",
            "Bulk discount available for orders over 10 units.",
            "Compatible with multiple vehicle models.",
            "Installation instructions included.",
            "Made in Japan. High quality standards.",
            "Local stock. Available for immediate delivery.",
            "Best seller. Monitor stock levels closely.",
            "Seasonal item. Higher demand during rainy season.",
            "Price may fluctuate based on exchange rates.",
            "Limited stock. Reorder point is 5 units.",
            "New product. Monitor customer feedback.",
        ];
        
        return $notes[array_rand($notes)];
    }

    /**
     * Generate image URL for inventory item
     */
    private function generateImageUrl(string $partName): string
    {
        $partSlug = Str::slug($partName);
        $imageNames = [
            'brake-pads.jpg', 'engine-oil.jpg', 'oil-filter.jpg', 'spark-plugs.jpg',
            'battery.jpg', 'alternator.jpg', 'radiator.jpg', 'shock-absorbers.jpg',
            'tires.jpg', 'headlights.jpg', 'windshield-wipers.jpg', 'ac-compressor.jpg'
        ];
        
        return 'https://example.com/images/' . ($imageNames[array_rand($imageNames)] ?? 'default-part.jpg');
    }

    /**
     * Generate barcode for inventory item
     */
    private function generateBarcode(string $partNumber): string
    {
        return 'BC' . str_pad(crc32($partNumber), 10, '0', STR_PAD_LEFT);
    }

    /**
     * Update supplier statistics based on inventory items
     */
    private function updateSupplierStatistics()
    {
        $suppliers = DB::table('inventory_suppliers')->get();
        
        foreach ($suppliers as $supplier) {
            $inventoryStats = DB::table('inventory')
                ->where('supplier_id', $supplier->id)
                ->selectRaw('COUNT(*) as item_count, SUM(quantity) as total_quantity, SUM(quantity * cost_price) as total_value')
                ->first();
            
            // Update supplier notes with inventory statistics
            $newNotes = "Supplies " . ($inventoryStats->item_count ?? 0) . " inventory items. " .
                       "Total inventory value: ₱" . number_format($inventoryStats->total_value ?? 0, 2) . ". " .
                       "Total quantity in stock: " . ($inventoryStats->total_quantity ?? 0) . " units.";
            
            DB::table('inventory_suppliers')
                ->where('id', $supplier->id)
                ->update([
                    'notes' => $newNotes . (empty($supplier->notes) ? '' : "\n\n" . $supplier->notes),
                    'updated_at' => now()
                ]);
        }
        
        $this->info("Updated statistics for {$suppliers->count()} suppliers.");
    }
}