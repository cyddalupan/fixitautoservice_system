<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('work_orders', 'service_type')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->string('service_type', 50)->nullable()->after('work_order_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
