<?php

// database/migrations/2025_08_18_000000_create_video_resources_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('video_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('videos')->cascadeOnDelete();

            $table->string('title', 150);
            $table->text('description')->nullable();

            // 1=file, 2=video, 3=image
            $table->tinyInteger('type');

            $table->string('file_src')->nullable();   // pdf/doc/docx
            $table->string('video_src')->nullable();  // mp4
            $table->string('img_src')->nullable();    // jpg/png/webp

            $table->date('uploaded')->nullable();
            $table->timestamps();

            $table->index(['video_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_resources');
    }
};
