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
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            // Add service_id for workflow linking
            $table->string('service_id')->nullable()->after('id');
            
            // Add index for faster queries
            $table->index('service_id');
            
            // Add foreign key constraint if services table exists
            if (Schema::hasTable('services')) {
                $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }
};
