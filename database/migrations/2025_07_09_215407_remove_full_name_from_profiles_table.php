<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFullNameFromProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('full_name')->nullable();
        });
    }
}
