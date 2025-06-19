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
        Schema::table('teachers', function (Blueprint $table) {
             $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('teacher_id')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone');
            $table->string('specialty');
            $table->string('address');
            $table->string('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            //
        });
    }
};
