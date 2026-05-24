<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mail_host')->default('smtp.gmail.com');
            $table->integer('mail_port')->default(587);
            $table->string('mail_from_address')->default('noreply@app.fixitautoservices.com');
            $table->string('mail_from_name')->default('Fix-It Auto Services');
            $table->string('mail_username')->nullable();
            $table->text('mail_password_encrypted')->nullable();
            $table->string('mail_encryption')->default('tls');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
