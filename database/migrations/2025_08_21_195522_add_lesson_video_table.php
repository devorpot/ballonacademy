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
        Schema::create('lesson_videos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Evita duplicar el mismo video en la misma lección
            $table->unique(['lesson_id', 'video_id']);

            // Índices útiles
            $table->index(['lesson_id', 'order']);
            $table->index(['course_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_videos');
    }
};
