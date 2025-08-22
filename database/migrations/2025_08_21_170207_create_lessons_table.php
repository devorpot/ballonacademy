<?php

// database/migrations/2025_08_21_000000_create_lessons_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            // Orden y estado
            $table->integer('order')->default(0);
            $table->boolean('active')->default(false);

            // Contenido
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->string('description_short')->nullable();

            // Publicación
            $table->date('publish_on');

            // Relaciones
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            // Banderas de anexos
            $table->boolean('add_video')->default(true);
            $table->boolean('add_evaluation')->default(false);
            $table->boolean('add_materials')->default(false);

            // Listas de IDs (JSON)
            $table->json('videos')->nullable();       // array de video_id (solo si add_video = true)
            $table->json('evaluations')->nullable();  // array de evaluation_id (solo si add_evaluation = true)
            $table->json('materials')->nullable();    // array de material_id (solo si add_materials = true)

            $table->timestamps();
            $table->softDeletes();

            // Índices útiles
            $table->index(['course_id', 'publish_on']);
            $table->index(['teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
