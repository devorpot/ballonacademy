<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_comments', function (Blueprint $table) {
            $table->boolean('active')
                  ->default(1)
                  ->after('dislikes'); // lo coloca después de dislikes
        });
    }

    public function down(): void
    {
        Schema::table('video_comments', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
