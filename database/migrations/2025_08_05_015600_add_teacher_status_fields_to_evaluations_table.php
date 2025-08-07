<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeacherStatusFieldsToEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('course_id');
            $table->enum('status', ['SEND', 'REVISION', 'APROVEED', 'NO APROVEED'])->default('SEND')->after('eva_comments');
            $table->date('date_evaluation')->nullable()->after('eva_send_date');

            // Si quieres relación
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
            $table->dropColumn('status');
            $table->dropColumn('date_evaluation');
        });
    }
}
