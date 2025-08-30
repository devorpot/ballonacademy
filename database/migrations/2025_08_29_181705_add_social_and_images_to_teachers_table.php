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
        $table->string('facebook')->nullable()->after('country');
        $table->string('instagram')->nullable()->after('facebook');
        $table->string('tiktok')->nullable()->after('instagram');
        $table->string('website')->nullable()->after('tiktok');
        $table->string('profile_image')->nullable()->after('website');
        $table->string('cover_image')->nullable()->after('profile_image');
    });
}

public function down(): void
{
    Schema::table('teachers', function (Blueprint $table) {
        $table->dropColumn([
            'facebook',
            'instagram',
            'tiktok',
            'website',
            'profile_image',
            'cover_image',
        ]);
    });
}

};
