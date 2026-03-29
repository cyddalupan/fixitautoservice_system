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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('created_via_form')->default(false)->after('is_active');
            $table->string('form_token', 64)->nullable()->after('created_via_form');
            $table->timestamp('form_submitted_at')->nullable()->after('form_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['created_via_form', 'form_token', 'form_submitted_at']);
        });
    }
};
