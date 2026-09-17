<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bug #2c: store() failed validation because service_advisor_id was
     * required, but the DB also enforced NOT NULL. No service_advisor users
     * exist yet, so creating a job order was impossible ("nothing happens").
     * Make the column nullable to match the nullable validation rule.
     */
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->foreignId('service_advisor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->foreignId('service_advisor_id')->nullable(false)->change();
        });
    }
};
