<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('estimates', 'service_type')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->string('service_type', 50)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
