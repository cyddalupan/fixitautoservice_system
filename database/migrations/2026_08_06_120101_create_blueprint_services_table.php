<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blueprint-compliant `services` catalog table.
 *
 * Source of truth: Fixit Blueprint card — simple services table:
 *   id, name, description, default_price (decimal), category, is_active
 * NO break-out by brand/model (the blueprint explicitly drops that).
 *
 * NOTE: This is a NEW table for the blueprint's /admin/services catalog.
 * It is intentionally separate from the legacy `Service` model which is
 * mapped to the `appointments` table (old app) and from service_pricings.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('services')) {
            return;
        }

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_price', 10, 2)->default(0);
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
