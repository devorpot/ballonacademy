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
    Schema::create('activations', function (Blueprint $table) {
        $table->id();
        $table->string('name', 120);
        $table->string('email', 190)->unique();
        $table->string('phone', 40)->nullable();
        $table->string('code', 6)->unique();
        $table->string('hash', 64)->unique();
        $table->boolean('active')->default(false);
        // Campo solicitado explícito
        $table->timestamp('created')->useCurrent();
        $table->timestamps(); // opcional pero recomendado
        $table->index('hash');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activations');
    }
};
