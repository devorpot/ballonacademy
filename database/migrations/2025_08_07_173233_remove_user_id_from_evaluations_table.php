<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // Si tienes clave foránea, primero elimínala
            if (Schema::hasColumn('evaluations', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();

            // Agrega la foreign key si es necesaria (ajusta según tu relación)
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
