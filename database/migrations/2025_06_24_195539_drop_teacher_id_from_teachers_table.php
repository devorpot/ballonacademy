<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('teacher_id')->unique()->nullable(); 
            // Ajusta el tipo y restricciones si lo necesitas exactamente igual a como estaba
        });
    }
};
