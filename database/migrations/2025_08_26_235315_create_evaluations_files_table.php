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
        Schema::create('evaluations_files', function (Blueprint $table) {
            $table->id();

            // Relación con evaluations
            $table->foreignId('evaluation_id')
                ->constrained('evaluations')
                ->cascadeOnDelete();

            // Orden del archivo
            $table->integer('order')->default(0);

            // Título y descripción
            $table->string('title');
            $table->text('description')->nullable();

            // Archivo subido y tipo
            $table->string('file_uploaded'); // ruta del archivo subido
            $table->string('type', 50);      // tipo MIME o extensión

            // Tamaño del archivo
            $table->unsignedBigInteger('size')->default(0); // tamaño en bytes

            // Fecha de carga
            $table->date('uploaded');

            // Timestamps de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations_files');
    }
};
