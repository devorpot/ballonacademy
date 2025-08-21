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
        // Elimina la tabla completa
        Schema::dropIfExists('activations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Si quieres, puedes recrear la tabla (estructura mínima de ejemplo)
        Schema::create('activations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
