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
    Schema::create('subscriptions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('course_id')->constrained()->onDelete('cascade');

        $table->decimal('payment_amount', 10, 2);
        $table->string('payment_currency', 10);
        $table->string('payment_description')->nullable();
        $table->string('payment_type')->nullable();
        $table->string('payment_card')->nullable();
        $table->string('payment_card_type')->nullable();
        $table->string('payment_card_brand')->nullable();
        $table->string('payment_bank')->nullable();
        $table->date('payment_date')->nullable();
        $table->date('payment_refund_date')->nullable();
        $table->string('payment_refund_desc')->nullable();
        $table->string('payment_status')->default('pendiente');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
