<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Haz NULL los campos personales que ya no usarás aquí
            $table->string('firstname')->nullable()->change();
            $table->string('lastname')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('address')->nullable()->change();
            // agrega aquí cualquier otro campo personal que exista
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('firstname')->nullable(false)->change();
            $table->string('lastname')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
        });
    }
};
