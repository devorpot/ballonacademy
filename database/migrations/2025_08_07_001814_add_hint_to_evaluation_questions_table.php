<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('evaluation_questions', function (Blueprint $table) {
        $table->json('options_json')->nullable()->after('question');
    });
}

public function down()
{
    Schema::table('evaluation_questions', function (Blueprint $table) {
        $table->dropColumn('options_json');
    });
}


};
