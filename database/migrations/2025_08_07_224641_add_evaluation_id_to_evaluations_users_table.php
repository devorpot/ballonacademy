<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluations_users', function (Blueprint $table) {
            $table->unsignedBigInteger('evaluation_id')->after('id')->nullable();

            // Si tienes la tabla evaluations y quieres la relación
            $table->foreign('evaluation_id')->references('id')->on('evaluations')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('evaluations_users', function (Blueprint $table) {
            $table->dropForeign(['evaluation_id']);
            $table->dropColumn('evaluation_id');
        });
    }
};
