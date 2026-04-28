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
            // Link to CRM
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();

            // New fields from form expansion
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('barangay')->nullable()->after('city');
            $table->string('color')->nullable()->after('vehicle_year');
            $table->string('engine_type')->nullable()->after('color');
            $table->string('transmission')->nullable()->after('engine_type');
            $table->string('chassis_number')->nullable()->after('transmission');
            $table->integer('mileage')->nullable()->after('chassis_number');

            // New statuses for the pipeline
            DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('new_lead','contacted','converted_to_customer','appointment_booked','won','lost','archived') NOT NULL DEFAULT 'new_lead'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn([
                'customer_id',
                'vehicle_id',
                'address',
                'city',
                'barangay',
                'color',
                'engine_type',
                'transmission',
                'chassis_number',
                'mileage',
            ]);
            DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('pending','reviewed','contacted','converted','rejected') NOT NULL DEFAULT 'pending'");
        });
    }
};
