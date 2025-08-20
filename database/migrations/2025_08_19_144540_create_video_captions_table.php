<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_captions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->string('lang', 10); // ej: es, en, fr, es-MX
            $table->string('file')->nullable(); // ruta del archivo .vtt/.srt guardado en storage
            $table->longText('captions')->nullable(); // opcional: guardar contenido del archivo
            $table->timestamps();

            // Foreign key hacia videos
            $table->foreign('video_id')
                ->references('id')->on('videos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_captions');
    }
};
