<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('job_orders', 'service_type')) {
            Schema::table('job_orders', function (Blueprint $table) {
                $table->string('service_type', 50)->nullable()->after('job_order_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
