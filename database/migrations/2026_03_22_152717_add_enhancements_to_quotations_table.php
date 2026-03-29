<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // License Plate and VIN
            $table->string('license_plate')->nullable()->after('vehicle_year');
            $table->string('vin_number')->nullable()->after('license_plate');
            
            // Preferred Appointment
            $table->date('preferred_date')->nullable()->after('vin_number');
            $table->string('preferred_time')->nullable()->after('preferred_date');
            
            // Service Checklist (store as JSON)
            $table->json('service_checklist')->nullable()->after('service_type');
            
            // Parts Preference
            $table->string('parts_preference')->nullable()->after('service_checklist');
            
            // Budget Range
            $table->decimal('budget_min', 10, 2)->nullable()->after('parts_preference');
            $table->decimal('budget_max', 10, 2)->nullable()->after('budget_min');
            
            // Photos (store as JSON array of filenames)
            $table->json('photos')->nullable()->after('budget_max');
            
            // Consent
            $table->boolean('consent_contact')->default(false)->after('photos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'license_plate',
                'vin_number',
                'preferred_date',
                'preferred_time',
                'service_checklist',
                'parts_preference',
                'budget_min',
                'budget_max',
                'photos',
                'consent_contact'
            ]);
        });
    }
};
