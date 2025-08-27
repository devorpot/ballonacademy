<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_activities', function (Blueprint $table) {
            $table->id();

            // Claves foráneas
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();

            // Datos de la actividad
            $table->string('activity', 50);        // ej: started, progress, ended
            $table->boolean('ended')->default(false);
            $table->timestamp('activity_date')->useCurrent();

            $table->timestamps();

            // Índices útiles
            $table->index(['user_id', 'course_id', 'lesson_id']);
            $table->index('activity_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_activities');
    }
};
