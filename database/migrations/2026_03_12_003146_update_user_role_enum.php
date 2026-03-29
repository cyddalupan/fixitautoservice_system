<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change role column from ENUM to VARCHAR
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('customer')->change();
        });
        
        // Update existing roles to match new structure
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('users')->where('role', 'manager')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'service_advisor')->update(['role' => 'office_staff']);
        // technician remains technician
        // customer remains customer
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update roles back to original values
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'admin')->update(['role' => 'manager']);
        DB::table('users')->where('role', 'office_staff')->update(['role' => 'service_advisor']);
        
        // Change role column back to ENUM (optional, but we'll keep as VARCHAR for flexibility)
        // Note: Changing back to ENUM would require checking all values first
    }
};
