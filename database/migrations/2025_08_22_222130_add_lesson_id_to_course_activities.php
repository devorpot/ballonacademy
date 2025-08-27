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
        Schema::table('course_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('lesson_id')->nullable()->after('user_id');

            // Si quieres llave foránea con cascade
            $table->foreign('lesson_id')
                  ->references('id')
                  ->on('lessons')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_activities', function (Blueprint $table) {
            //
        });
    }
};
