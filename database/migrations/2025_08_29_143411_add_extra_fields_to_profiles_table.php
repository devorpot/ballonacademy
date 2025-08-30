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
        Schema::table('profiles', function (Blueprint $table) {
         
            $table->boolean('activity')->default(false)->after('description');
            $table->boolean('experiencie')->default(false)->after('activity');
            $table->string('bussines_own')->nullable()->after('experiencie');
            $table->string('bussines_name')->nullable()->after('bussines_own');
            $table->string('bussines_logo')->nullable()->after('bussines_name'); // guarda path del archivo
            $table->string('bussines_website')->nullable()->after('bussines_logo');
            $table->string('bussines_category')->nullable()->after('bussines_website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                 
               
                'activity',
                'experiencie',
                'bussines_own',
                'bussines_name',
                'bussines_logo',
                'bussines_website',
                'bussines_category',
            ]);
        });
    }
};
