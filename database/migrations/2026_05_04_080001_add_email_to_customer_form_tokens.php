<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('customer_form_tokens', 'email')) {
            Schema::table('customer_form_tokens', function (Blueprint $table) {
                $table->string('email', 255)->nullable()->after('generated_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customer_form_tokens', 'email')) {
            Schema::table('customer_form_tokens', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};
