<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if we should run HR Payroll sample data
        if (env('SEED_HR_PAYROLL_SAMPLE', false)) {
            $this->call(HrPayrollSampleDataSeeder::class);
        }
        
        // You can add other seeders here as needed
    }
}