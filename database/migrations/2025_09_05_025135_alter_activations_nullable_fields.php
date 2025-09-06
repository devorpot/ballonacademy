<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            // Hacemos opcionales todos excepto name, email, phone, course_id
            $table->string('size')->nullable()->change();
            $table->string('street')->nullable()->change();
           
            $table->string('neighborhood', 120)->nullable()->change();
            $table->string('postal_code', 20)->nullable()->change();
            $table->string('city', 120)->nullable()->change();
            $table->string('country', 80)->nullable()->change();
            $table->string('state', 120)->nullable()->change();
            $table->string('facebook')->nullable()->change();
            $table->string('tiktok')->nullable()->change();
            $table->string('instagram')->nullable()->change();
            $table->string('occupation', 180)->nullable()->change();
            $table->enum('experience', ['Sí', 'No'])->nullable()->change();
            $table->enum('has_business', ['Sí', 'No'])->nullable()->change();
            $table->string('business_name', 180)->nullable()->change();
            $table->string('business_type', 180)->nullable()->change();
            $table->string('password_hash')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            // Revertimos (volver a NOT NULL si hiciera falta)
            $table->string('size')->nullable(false)->change();
            $table->string('street')->nullable(false)->change();
            
            $table->string('neighborhood', 120)->nullable(false)->change();
            $table->string('postal_code', 20)->nullable(false)->change();
            $table->string('city', 120)->nullable(false)->change();
            $table->string('country', 80)->nullable(false)->change();
            $table->string('state', 120)->nullable(false)->change();
            $table->string('facebook')->nullable(false)->change();
            $table->string('tiktok')->nullable(false)->change();
            $table->string('instagram')->nullable(false)->change();
            $table->string('occupation', 180)->nullable(false)->change();
            $table->enum('experience', ['Sí', 'No'])->nullable(false)->change();
            $table->enum('has_business', ['Sí', 'No'])->nullable(false)->change();
            $table->string('business_name', 180)->nullable(false)->change();
            $table->string('business_type', 180)->nullable(false)->change();
            $table->string('password_hash')->nullable(false)->change();
        });
    }
};
