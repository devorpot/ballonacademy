<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            // Ajusta tipos y longitudes a lo que tengas en tu esquema
            $table->string('street')->nullable()->change();
            $table->string('number_int', 50)->nullable()->change();
            $table->string('number_ext', 50)->nullable()->change();
            $table->string('neighborhood', 120)->nullable()->change();
            $table->string('city', 120)->nullable()->change();
            $table->string('state', 120)->nullable()->change();
            $table->string('zip', 20)->nullable()->change();
            $table->string('country', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            $table->string('street')->nullable(false)->change();
            $table->string('number_int', 50)->nullable(false)->change();
            $table->string('number_ext', 50)->nullable(false)->change();
            $table->string('neighborhood', 120)->nullable(false)->change();
            $table->string('city', 120)->nullable(false)->change();
            $table->string('state', 120)->nullable(false)->change();
            $table->string('zip', 20)->nullable(false)->change();
            $table->string('country', 120)->nullable(false)->change();
        });
    }
};
