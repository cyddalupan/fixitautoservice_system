<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\ServiceRecord;
use App\Models\CustomerNote;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@fixitautoservices.com',
            'password' => Hash::make('FixitAdmin2024!'),
            'role' => 'admin',
            'phone' => '+1 (555) 123-4567',
            'employee_id' => 'EMP001',
            'hire_date' => '2024-01-15',
            'skills' => ['management', 'customer_service', 'technical_analysis'],
            'certifications' => ['ASE Master Technician', 'EPA 609'],
            'hourly_rate' => 75.00,
            'is_active' => true,
        ]);

        // Create manager
        $manager = User::create([
            'name' => 'Shop Manager',
            'email' => 'manager@fixitautoservices.com',
            'password' => Hash::make('FixitManager2024!'),
            'role' => 'manager',
            'phone' => '+1 (555) 123-4568',
            'employee_id' => 'EMP002',
            'hire_date' => '2024-02-01',
            'skills' => ['team_management', 'inventory', 'scheduling'],
            'certifications' => ['ASE Service Consultant'],
            'hourly_rate' => 65.00,
            'is_active' => true,
        ]);

        // Create service advisors
        $advisor1 = User::create([
            'name' => 'Service Advisor 1',
            'email' => 'advisor1@fixitautoservices.com',
            'password' => Hash::make('FixitAdvisor2024!'),
            'role' => 'service_advisor',
            'phone' => '+1 (555) 123-4569',
            'employee_id' => 'EMP003',
            'hire_date' => '2024-03-01',
            'skills' => ['customer_service', 'estimates', 'parts_ordering'],
            'certifications' => ['ASE Service Consultant'],
            'hourly_rate' => 45.00,
            'is_active' => true,
        ]);

        $advisor2 = User::create([
            'name' => 'Service Advisor 2',
            'email' => 'advisor2@fixitautoservices.com',
            'password' => Hash::make('FixitAdvisor2024!'),
            'role' => 'service_advisor',
            'phone' => '+1 (555) 123-4570',
            'employee_id' => 'EMP004',
            'hire_date' => '2024-03-15',
            'skills' => ['customer_service', 'warranty', 'insurance'],
            'certifications' => ['ASE Service Consultant'],
            'hourly_rate' => 45.00,
            'is_active' => true,
        ]);

        // Create technicians
        $tech1 = User::create([
            'name' => 'Master Technician',
            'email' => 'tech1@fixitautoservices.com',
            'password' => Hash::make('FixitTech2024!'),
            'role' => 'technician',
            'phone' => '+1 (555) 123-4571',
            'employee_id' => 'EMP005',
            'hire_date' => '2024-01-20',
            'skills' => ['engine_repair', 'transmission', 'electrical'],
            'certifications' => ['ASE Master Technician', 'ASE Engine Repair', 'ASE Electrical'],
            'hourly_rate' => 55.00,
            'is_active' => true,
        ]);

        $tech2 = User::create([
            'name' => 'Brake Specialist',
            'email' => 'tech2@fixitautoservices.com',
            'password' => Hash::make('FixitTech2024!'),
            'role' => 'technician',
            'phone' => '+1 (555) 123-4572',
            'employee_id' => 'EMP006',
            'hire_date' => '2024-02-10',
            'skills' => ['brakes', 'suspension', 'alignment'],
            'certifications' => ['ASE Brakes', 'ASE Suspension & Steering'],
            'hourly_rate' => 50.00,
            'is_active' => true,
        ]);

        // Create customers
        $customer1 = Customer::create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'phone' => '+1 (555) 987-6543',
            'address' => '123 Main Street',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '90210',
            'customer_type' => 'individual',
            'credit_limit' => 5000.00,
            'balance' => 0.00,
            'payment_terms' => 'net_30',
            'is_active' => true,
            'customer_since' => '2023-05-15',
            'loyalty_points' => 1250,
            'preferred_contact' => 'email',
            'segment' => 'premium',
        ]);

        $customer2 = Customer::create([
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'sarah.j@example.com',
            'phone' => '+1 (555) 987-6544',
            'address' => '456 Oak Avenue',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '90211',
            'customer_type' => 'individual',
            'credit_limit' => 3000.00,
            'balance' => 0.00,
            'payment_terms' => 'net_15',
            'is_active' => true,
            'customer_since' => '2023-08-22',
            'loyalty_points' => 850,
            'preferred_contact' => 'sms',
            'segment' => 'regular',
        ]);

        $customer3 = Customer::create([
            'first_name' => 'ABC',
            'last_name' => 'Delivery',
            'email' => 'fleet@abcdelivery.com',
            'phone' => '+1 (555) 987-6545',
            'address' => '789 Business Blvd',
            'city' => 'Anytown',
            'state' => 'CA',
            'zip_code' => '90212',
            'customer_type' => 'fleet',
            'company_name' => 'ABC Delivery Services',
            'tax_id' => '12-3456789',
            'credit_limit' => 25000.00,
            'balance' => 0.00,
            'payment_terms' => 'net_30',
            'is_active' => true,
            'customer_since' => '2022-11-05',
            'loyalty_points' => 3500,
            'preferred_contact' => 'phone',
            'segment' => 'commercial',
        ]);

        // Create vehicles for customers
        $vehicle1 = Vehicle::create([
            'customer_id' => $customer1->id,
            'vin' => '1HGCM82633A123456',
            'license_plate' => 'ABC123',
            'make' => 'Honda',
            'model' => 'Accord',
            'year' => 2020,
            'color' => 'Silver',
            'vehicle_type' => 'car',
            'engine_type' => '2.0L 4-cylinder',
            'transmission' => 'Automatic',
            'fuel_type' => 'Gasoline',
            'odometer' => 45230,
            'last_service_date' => '2024-01-15',
            'next_service_date' => '2024-07-15',
            'service_interval_miles' => 7500,
            'service_interval_months' => 6,
            'average_service_cost' => 350.00,
            'total_service_count' => 4,
            'has_warranty' => true,
            'warranty_expiry' => '2025-12-31',
            'is_active' => true,
        ]);

        $vehicle2 = Vehicle::create([
            'customer_id' => $customer1->id,
            'vin' => '5XYZU3LBXKG123457',
            'license_plate' => 'DEF456',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2019,
            'color' => 'Blue',
            'vehicle_type' => 'car',
            'engine_type' => '2.5L 4-cylinder',
            'transmission' => 'Automatic',
            'fuel_type' => 'Hybrid',
            'odometer' => 68250,
            'last_service_date' => '2024-02-10',
            'next_service_date' => '2024-08-10',
            'service_interval_miles' => 10000,
            'service_interval_months' => 12,
            'average_service_cost' => 420.00,
            'total_service_count' => 3,
            'has_warranty' => false,
            'is_active' => true,
        ]);

        $vehicle3 = Vehicle::create([
            'customer_id' => $customer2->id,
            'vin' => '3FA6P0HD9KR123458',
            'license_plate' => 'GHI789',
            'make' => 'Ford',
            'model' => 'Escape',
            'year' => 2021,
            'color' => 'Red',
            'vehicle_type' => 'suv',
            'engine_type' => '1.5L EcoBoost',
            'transmission' => 'Automatic',
            'fuel_type' => 'Gasoline',
            'odometer' => 28750,
            'last_service_date' => '2024-03-05',
            'next_service_date' => '2024-09-05',
            'service_interval_miles' => 7500,
            'service_interval_months' => 6,
            'average_service_cost' => 380.00,
            'total_service_count' => 2,
            'has_warranty' => true,
            'warranty_expiry' => '2026-03-05',
            'is_active' => true,
        ]);

        // Create service records
        $service1 = ServiceRecord::create([
            'vehicle_id' => $vehicle1->id,
            'customer_id' => $customer1->id,
            'service_date' => '2024-01-15',
            'odometer_at_service' => 42500,
            'service_type' => 'Scheduled Maintenance',
            'description' => 'Oil change, tire rotation, brake inspection',
            'labor_cost' => 120.00,
            'parts_cost' => 85.50,
            'total_cost' => 205.50,
            'tax_amount' => 16.44,
            'discount_amount' => 10.00,
            'final_amount' => 211.94,
            'payment_status' => 'paid',
            'service_status' => 'completed',
            'technician_id' => $tech1->id,
            'service_advisor_id' => $advisor1->id,
            'work_order_number' => 'WO-2024-00123',
            'diagnosis' => 'Vehicle in good condition. Brake pads at 60% life.',
            'recommendations' => 'Next service due in 6 months or 7,500 miles',
            'parts_used' => ['Oil Filter', 'Synthetic Oil 5W-30'],
            'next_service_date' => '2024-07-15',
            'next_service_odometer' => 50000,
            'customer_rating' => 5,
            'customer_feedback' => 'Excellent service, very professional.',
        ]);

        $service2 = ServiceRecord::create([
            'vehicle_id' => $vehicle2->id,
            'customer_id' => $customer1->id,
            'service_date' => '2024-02-10',
            'odometer_at_service' => 65000,
            'service_type' => 'Brake Service',
            'description' => 'Replace front brake pads and rotors',
            'labor_cost' => 180.00,
            'parts_cost' => 245.75,
            'total_cost' => 425.75,
            'tax_amount' => 34.06,
            'discount_amount' => 0.00,
            'final_amount' => 459.81,
            'payment_status' => 'paid',
            'service_status' => 'completed',
            'technician_id' => $tech2->id,
            'service_advisor_id' => $advisor2->id,
            'work_order_number' => 'WO-2024-00145',
            'diagnosis' => 'Front brake pads worn to 15%, rotors scored',
            'recommendations' => 'Monitor rear brakes, next inspection in 6 months',
            'parts_used' => ['Brake Pads (Front)', 'Brake Rotors (Front)'],
            'next_service_date' => '2024-08-10',
            'next_service_odometer' => 75000,
            'customer_rating' => 4,
            'customer_feedback' => 'Good work, but took longer than estimated.',
        ]);

        $service3 = ServiceRecord::create([
            'vehicle_id' => $vehicle3->id,
            'customer_id' => $customer2->id,
            'service_date' => '2024-03-05',
            'odometer_at_service' => 27500,
            'service_type' => 'Electrical Repair',
            'description' => 'Replace battery and alternator',
            'labor_cost' => 220.00,
            'parts_cost' => 385.25,
            'total_cost' => 605.25,
            'tax_amount' => 48.42,
            'discount_amount' => 25.00,
            'final_amount' => 628.67,
            'payment_status' => 'partial',
            'service_status' => 'completed',
            'technician_id' => $tech1->id,
            'service_advisor_id' => $advisor1->id,
            'work_order_number' => 'WO-2024-00167',
            'diagnosis' => 'Battery failed load test, alternator not charging properly',
            'recommendations' => 'Check electrical system in 3 months',
            'parts_used' => ['Battery', 'Alternator'],
            'warranty_work' => true,
            'warranty_type' => 'Manufacturer',
            'next_service_date' => '2024-09-05',
            'next_service_odometer' => 35000,
            'customer_rating' => 5,
            'customer_feedback' => 'Fixed my car quickly, very satisfied!',
        ]);

        // Create customer notes
        CustomerNote::create([
            'customer_id' => $customer1->id,
            'user_id' => $advisor1->id,
            'note_type' => 'preference',
            'content' => 'Customer prefers morning appointments and wants text reminders.',
            'is_important' => true,
            'requires_follow_up' => false,
            'tags' => ['preferences', 'communication'],
        ]);

        CustomerNote::create([
            'customer_id' => $customer1->id,
            'user_id' => $manager->id,
            'note_type' => 'compliment',
            'content' => 'Customer mentioned excellent service during last visit. Consider for loyalty reward.',
            'is_important' => false,
            'requires_follow_up' => true,
            'follow_up_date' => '2024-04-15',
            'tags' => ['feedback', 'loyalty'],
        ]);

        CustomerNote::create([
            'customer_id' => $customer2->id,
            'user_id' => $advisor2->id,
            'note_type' => 'reminder',
            'content' => 'Customer needs recall notice for airbag system. Follow up in 2 weeks.',
            'is_important' => true,
            'requires_follow_up' => true,
            'follow_up_date' => '2024-03-20',
            'tags' => ['recall', 'safety'],
        ]);

        CustomerNote::create([
            'customer_id' => $customer3->id,
            'user_id' => $admin->id,
            'note_type' => 'general',
            'content' => 'Fleet account - 5 vehicles total. Primary contact is John at extension 102.',
            'is_important' => true,
            'requires_follow_up' => false,
            'tags' => ['fleet', 'commercial', 'contacts'],
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@fixitautoservices.com / FixitAdmin2024!');
        $this->command->info('Manager login: manager@fixitautoservices.com / FixitManager2024!');
        $this->command->info('Service Advisor login: advisor1@fixitautoservices.com / FixitAdvisor2024!');
        $this->command->info('Technician login: tech1@fixitautoservices.com / FixitTech2024!');
    }
}