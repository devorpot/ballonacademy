<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('description_short')->nullable();
            $table->text('comments')->nullable();
            $table->string('image_cover')->nullable();
            $table->string('video_url');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('videos');
    }
};