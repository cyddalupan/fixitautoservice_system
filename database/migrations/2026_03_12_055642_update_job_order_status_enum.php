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
        // MySQL-only: raw enum ALTER. SQLite has no native ENUM type (columns are strings),
        // so enum migrations are a no-op on any non-MySQL driver.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // First, modify the column to use the new enum values
        DB::statement("
            ALTER TABLE job_orders 
            MODIFY COLUMN job_order_status 
            ENUM('pending', 'repairing', 'waiting_parts', 'completed', 'released', 'cancelled', 'draft', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'invoiced') 
            DEFAULT 'pending'
        ");
        
        // Now map old statuses to new statuses
        DB::statement("
            UPDATE job_orders 
            SET job_order_status = CASE 
                WHEN job_order_status IN ('draft', 'pending_approval') THEN 'pending'
                WHEN job_order_status = 'approved' THEN 'pending'
                WHEN job_order_status = 'in_progress' THEN 'repairing'
                WHEN job_order_status = 'on_hold' THEN 'waiting_parts'
                WHEN job_order_status = 'completed' THEN 'completed'
                WHEN job_order_status = 'invoiced' THEN 'released'
                ELSE 'cancelled'
            END
        ");
        
        // Finally, remove the old enum values
        DB::statement("
            ALTER TABLE job_orders 
            MODIFY COLUMN job_order_status 
            ENUM('pending', 'repairing', 'waiting_parts', 'completed', 'released', 'cancelled') 
            DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // MySQL-only: raw enum ALTER. No-op on non-MySQL drivers.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // First, add back the old enum values
        DB::statement("
            ALTER TABLE job_orders 
            MODIFY COLUMN job_order_status 
            ENUM('pending', 'repairing', 'waiting_parts', 'completed', 'released', 'cancelled', 'draft', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'invoiced') 
            DEFAULT 'pending'
        ");
        
        // Map new statuses back to old statuses
        DB::statement("
            UPDATE job_orders 
            SET job_order_status = CASE 
                WHEN job_order_status = 'pending' THEN 'draft'
                WHEN job_order_status = 'repairing' THEN 'in_progress'
                WHEN job_order_status = 'waiting_parts' THEN 'on_hold'
                WHEN job_order_status = 'completed' THEN 'completed'
                WHEN job_order_status = 'released' THEN 'invoiced'
                ELSE 'cancelled'
            END
        ");
        
        // Finally, revert to original enum values
        DB::statement("
            ALTER TABLE job_orders 
            MODIFY COLUMN job_order_status 
            ENUM('draft', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'completed', 'cancelled', 'invoiced') 
            DEFAULT 'draft'
        ");
    }
};
