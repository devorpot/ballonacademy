<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->string('pdf_file')->nullable()->after('status'); // ajusta "status" por la última columna que tengas
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('pdf_file');
        });
    }
};
