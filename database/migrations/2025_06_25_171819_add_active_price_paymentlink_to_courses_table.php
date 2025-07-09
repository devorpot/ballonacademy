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
            Schema::table('courses', function (Blueprint $table) {
                $table->boolean('active')->default(true);
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('payment_link')->nullable();
            });
        }

        public function down()
        {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn(['active', 'price', 'payment_link']);
            });
        }
};
