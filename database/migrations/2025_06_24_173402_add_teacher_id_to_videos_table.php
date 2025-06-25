<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('teacher_id')
                  ->nullable() // el maestro es opcional al inicio
                  ->constrained('teachers') // referencia a teachers.id
                  ->nullOnDelete(); // si se borra el maestro, teacher_id se pone en null
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
