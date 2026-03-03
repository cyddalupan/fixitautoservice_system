<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateSupplierSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suppliers:generate-sample-data {--count=10 : Number of suppliers to create} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample data for inventory suppliers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $force = $this->option('force');

        // Check if there's existing data
        $existingCount = DB::table('inventory_suppliers')->count();
        
        if ($existingCount > 0 && !$force) {
            if (!$this->confirm("There are already {$existingCount} suppliers in the database. Do you want to delete them and create new sample data?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
            
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('inventory_suppliers')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->info("Deleted {$existingCount} existing suppliers.");
        }

        $this->info("Generating {$count} sample suppliers...");

        $suppliers = $this->generateSuppliers($count);

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        foreach ($suppliers as $supplier) {
            DB::table('inventory_suppliers')->insert($supplier);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully created {$count} sample suppliers!");
        $this->info("📊 Statistics:");
        $this->info("   • Total suppliers: " . DB::table('inventory_suppliers')->count());
        $this->info("   • Active suppliers: " . DB::table('inventory_suppliers')->where('is_active', 1)->count());
        $this->info("   • Preferred suppliers: " . DB::table('inventory_suppliers')->where('is_preferred', 1)->count());
        $this->info("   • Average credit limit: ₱" . number_format(DB::table('inventory_suppliers')->avg('credit_limit') ?? 0, 2));
        
        $this->newLine();
        $this->info("🔗 You can view the suppliers at: https://app.fixitautoservices.com/suppliers-management");

        return 0;
    }

    /**
     * Generate sample suppliers data
     */
    private function generateSuppliers(int $count): array
    {
        $suppliers = [];
        $now = now();

        // Common automotive parts suppliers in the Philippines
        $supplierNames = [
            'Auto Parts Philippines Corp.',
            'Motorix Auto Supply',
            'Car Parts Depot',
            'Mighty Auto Supply',
            'Speedlab Performance',
            'Ziebart Philippines',
            'Bridgestone Philippines',
            'Goodyear Philippines',
            'Mitsubishi Motors Philippines',
            'Toyota Motor Philippines',
            'Honda Cars Philippines',
            'Ford Group Philippines',
            'Isuzu Philippines',
            'Nissan Philippines',
            'Hyundai Asia Resources',
            'Motor Image Pilipinas',
            'CATS Motors',
            'Auto Nation Group',
            'Transmission Specialist',
            'Brake Masters',
            'Suspension Experts',
            'Engine Parts Depot',
            'Electrical Systems Inc.',
            'AC Delco Philippines',
            'Bosch Philippines',
            'Denso Philippines',
            'NGK Philippines',
            'Mobil Philippines',
            'Shell Philippines',
            'Petron Corporation'
        ];

        // Philippine cities
        $cities = ['Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig', 'Mandaluyong', 'San Juan', 'Pasay', 'Parañaque', 'Las Piñas', 'Muntinlupa', 'Marikina', 'Caloocan', 'Malabon', 'Navotas', 'Valenzuela'];
        
        // Philippine states/provinces
        $states = ['Metro Manila', 'Cavite', 'Laguna', 'Batangas', 'Rizal', 'Bulacan', 'Pampanga', 'Bataan', 'Zambales'];
        
        // Payment terms
        $paymentTerms = ['Net 30', 'Net 60', 'COD', '50% Advance, 50% COD', 'Net 15', 'Net 45'];
        
        // Shipping methods
        $shippingMethods = ['LBC', 'J&T Express', '2GO', 'Air21', 'Lalamove', 'Pickup', 'Company Truck'];

        for ($i = 0; $i < $count; $i++) {
            $name = $supplierNames[$i % count($supplierNames)] . ($i > count($supplierNames) ? " Branch " . ceil(($i + 1) / count($supplierNames)) : '');
            $code = 'SUP' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            
            $suppliers[] = [
                'name' => $name,
                'code' => $code,
                'contact_name' => $this->generateFilipinoName(),
                'contact_email' => Str::slug($name) . '@example.com',
                'contact_phone' => '+639' . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'website' => 'https://www.' . Str::slug(str_replace([' ', '.', ','], '', $name)) . '.ph',
                'address' => $this->generateAddress(),
                'city' => $cities[array_rand($cities)],
                'state' => $states[array_rand($states)],
                'zip_code' => str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'country' => 'Philippines',
                'payment_terms' => $paymentTerms[array_rand($paymentTerms)],
                'credit_limit' => mt_rand(50000, 500000),
                'current_balance' => mt_rand(0, 100000),
                'tax_id' => $this->generateTaxId(),
                'account_number' => 'ACC' . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'shipping_method' => $shippingMethods[array_rand($shippingMethods)],
                'shipping_cost' => mt_rand(100, 1000),
                'lead_time_days' => mt_rand(1, 14),
                'discount_percentage' => mt_rand(0, 20),
                'is_preferred' => mt_rand(0, 1),
                'is_active' => mt_rand(0, 10) > 1 ? 1 : 0, // 90% active
                'notes' => $this->generateNotes($name),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $suppliers;
    }

    /**
     * Generate a Filipino name
     */
    private function generateFilipinoName(): string
    {
        $firstNames = ['Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Carmen', 'Antonio', 'Teresa', 'Francisco', 'Rosa', 'Manuel', 'Lourdes', 'Ricardo', 'Concepcion', 'Carlos', 'Josefa'];
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Torres', 'Dela Cruz', 'Ramos', 'Mendoza', 'Aquino', 'Castro', 'Romero', 'David', 'Mercado'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a Philippine address
     */
    private function generateAddress(): string
    {
        $streetNumbers = ['123', '456', '789', '100', '200', '300', '400', '500'];
        $streetNames = ['Rizal Avenue', 'Quezon Boulevard', 'Bonifacio Street', 'Aguinaldo Highway', 'Marcos Highway', 'Osmeña Highway', 'Roxas Boulevard', 'EDSA', 'C5', 'Ortigas Avenue'];
        $barangays = ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8'];
        
        return $streetNumbers[array_rand($streetNumbers)] . ' ' . 
               $streetNames[array_rand($streetNames)] . ', ' . 
               $barangays[array_rand($barangays)];
    }

    /**
     * Generate a Philippine TIN (Tax Identification Number)
     */
    private function generateTaxId(): string
    {
        return str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' .
               str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' .
               str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' .
               str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate notes for supplier
     */
    private function generateNotes(string $supplierName): string
    {
        $notes = [
            "Primary supplier for {$supplierName}. Reliable delivery and good quality parts.",
            "Specializes in OEM parts. Minimum order quantity applies.",
            "Offers bulk discounts for orders over ₱50,000.",
            "Known for fast shipping and excellent customer service.",
            "Preferred supplier for genuine parts. Slightly higher prices but guaranteed quality.",
            "Good for aftermarket parts. Competitive pricing.",
            "Specializes in performance parts and accessories.",
            "Wholesale prices available for registered businesses.",
            "Offers extended warranty on all parts.",
            "Local distributor for international brands.",
            "Provides technical support for complex installations.",
            "Monthly promotions and discounts available.",
            "Accepts various payment methods including credit cards.",
            "Free shipping for orders over ₱10,000.",
            "Same-day delivery available for Metro Manila orders.",
        ];
        
        return $notes[array_rand($notes)];
    }
}