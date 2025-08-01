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
    Schema::create('evaluations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('course_id');
        $table->date('eva_send_date')->nullable();
        $table->string('eva_video_file')->nullable(); // Ruta del video
        $table->longText('eva_comments')->nullable();
        $table->timestamps();

        // Claves foráneas
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
