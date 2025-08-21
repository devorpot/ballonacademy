<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('shirt_size')->nullable()->after('lastname'); // o la columna que prefieras como referencia
            $table->string('address')->nullable()->after('shirt_size');

            $table->string('phone')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['shirt_size', 'address', 'phone']);
        });
    }
};
