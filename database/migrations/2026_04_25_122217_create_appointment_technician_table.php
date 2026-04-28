<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_technician', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->nullable()->comment('e.g. Lead Tech, Assistant, Diagnostician');
            $table->timestamps();
            
            $table->unique(['appointment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_technician');
    }
};
