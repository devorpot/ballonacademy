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
    Schema::create('certificates', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('master_id')->constrained('teachers')->onDelete('cascade');

        $table->string('authorized_by');
        $table->date('date_start')->nullable();
        $table->date('date_end')->nullable();
        $table->date('date_expedition')->nullable();
        $table->string('comments')->nullable();
        $table->string('photo')->nullable();
        $table->string('logo')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('certificates');
}

};
