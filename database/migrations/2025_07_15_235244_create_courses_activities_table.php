<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('activity');
            $table->unsignedTinyInteger('ended')->default(0); // 0 = no terminado, 1 = terminado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses_activities');
    }
};
