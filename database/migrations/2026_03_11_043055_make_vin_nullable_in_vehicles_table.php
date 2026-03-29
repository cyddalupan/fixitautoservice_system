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
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['vin']);
            
            // Make VIN column nullable since it's optional in the form
            $table->string('vin')->nullable()->change();
            
            // Add back the unique constraint for non-null values
            $table->unique('vin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['vin']);
            
            // Revert VIN column to not nullable
            $table->string('vin')->nullable(false)->change();
            
            // Add back the unique constraint
            $table->unique('vin');
        });
    }
};
