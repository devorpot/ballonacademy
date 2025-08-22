<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('image')->nullable()->after('title');        // Ruta de la imagen principal
            $table->string('image_cover')->nullable()->after('image');  // Ruta de la imagen de portada
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['image', 'image_cover']);
        });
    }
};
