<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // 1=Course, 2=Video
            $table->unsignedTinyInteger('type')
                ->default(1)
                ->after('status')
                ->comment('1=Course,2=Video')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
