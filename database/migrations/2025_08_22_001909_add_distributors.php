<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // requerido
            $table->string('logo')->nullable(); // imagen ruta
            $table->text('description')->nullable();

            $table->string('country'); // requerido
            $table->string('state');   // requerido: enum con países
            $table->string('region')->nullable();
            $table->string('zone')->nullable();
            $table->string('address'); // requerido

            $table->string('gmap_link')->nullable(); // URL de Google Maps
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();

            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('phone')->nullable();

            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};
