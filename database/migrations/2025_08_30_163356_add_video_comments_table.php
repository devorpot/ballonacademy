<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('video_id');
            
            $table->longText('comment');
            $table->integer('likes')->nullable();
            $table->integer('dislikes')->nullable();
            
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_comments');
    }
};
