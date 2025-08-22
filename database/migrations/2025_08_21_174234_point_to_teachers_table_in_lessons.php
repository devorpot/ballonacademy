<?php

// migration para corregir FK a teachers
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            // Asegúrate de que el tipo coincida con teachers.id
            $table->unsignedBigInteger('teacher_id')->change();
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
