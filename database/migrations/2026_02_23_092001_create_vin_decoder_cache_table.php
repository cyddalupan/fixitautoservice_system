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
        Schema::create('vin_decoder_cache', function (Blueprint $table) {
            $table->id();
            $table->string('vin')->unique();
            $table->json('decoded_data');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->string('trim')->nullable();
            $table->string('engine')->nullable();
            $table->string('transmission')->nullable();
            $table->string('drive_type')->nullable();
            $table->string('body_style')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('plant_code')->nullable();
            $table->integer('cache_hits')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('vin');
            $table->index(['make', 'model', 'year']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vin_decoder_cache');
    }
};