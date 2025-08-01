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
    Schema::table('videos', function (Blueprint $table) {
        $table->string('size')->nullable()->after('video_path');
        $table->string('duration')->nullable()->after('size');
    });
}

public function down(): void
{
    Schema::table('videos', function (Blueprint $table) {
        $table->dropColumn(['size', 'duration']);
    });
}

};
