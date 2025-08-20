<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsUsersTable extends Migration
{
    public function up()
    {
        Schema::create('evaluations_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('video_id')->nullable()->constrained('videos');
            $table->text('comments')->nullable();
            $table->boolean('status')->default(false);
            $table->string('files')->nullable(); // puedes cambiar a json si necesitas múltiples archivos
            $table->foreignId('approved_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluations_users');
    }
}
