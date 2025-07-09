<?php

 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // MXN, USD, etc.
            $table->string('label');              // Peso Mexicano, Dólar, etc.
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('currencies');
    }
};
