<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')
                ->constrained('lessons')
                ->cascadeOnDelete();

            $table->foreignId('evaluation_id')
                ->constrained('evaluations')
                ->cascadeOnDelete();

            // Opcional: mantener referencia al curso para filtros/consultas
            $table->foreignId('course_id')
                ->nullable()
                ->constrained('courses')
                ->nullOnDelete();

            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Evita duplicados de la misma evaluación en la misma lección
            $table->unique(['lesson_id', 'evaluation_id']);

            // Índices útiles
            $table->index(['course_id']);
            $table->index(['lesson_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_evaluations');
    }
};
