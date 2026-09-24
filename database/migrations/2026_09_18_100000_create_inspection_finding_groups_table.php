<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inspection_finding_groups')) {
            Schema::create('inspection_finding_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_id')->constrained('vehicle_inspections')->cascadeOnDelete();
                $table->string('name', 150)->default('Group');
                $table->decimal('labor_cost', 10, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_finding_groups');
    }
};
