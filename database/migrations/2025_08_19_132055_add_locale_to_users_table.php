<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Código ISO corto: 'es', 'en', o variantes tipo 'en-US' (hasta 5 chars)
            $table->string('locale', 5)
                  ->default('es')
                  ->comment('User UI language, e.g. es | en | en-US');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
