<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->nullable()->change();
            $table->unsignedBigInteger('payment_currency')->nullable()->change();
            $table->string('payment_description', 2000)->nullable()->change();
            $table->string('payment_type', 50)->nullable()->change();
            $table->string('payment_card', 50)->nullable()->change();
            $table->string('payment_card_type', 50)->nullable()->change();
            $table->string('payment_card_brand', 50)->nullable()->change();
            $table->string('payment_bank', 100)->nullable()->change();
            $table->date('payment_date')->nullable()->change();
            $table->date('payment_refund_date')->nullable()->change();
            $table->string('payment_refund_desc', 2000)->nullable()->change();
            $table->string('payment_status', 50)->nullable()->change();
            $table->string('payment_stripe_id', 50)->nullable()->change();
            $table->boolean('payment_refund')->nullable()->change();
            $table->boolean('used_coupon')->nullable()->change();
            $table->string('coupon_id', 255)->nullable()->change();
            $table->decimal('coupon_discount', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Si quieres revertir, vuelves a ponerlas NOT NULL
            $table->decimal('payment_amount', 10, 2)->nullable(false)->change();
            $table->unsignedBigInteger('payment_currency')->nullable(false)->change();
            $table->string('payment_description', 2000)->nullable(false)->change();
            $table->string('payment_type', 50)->nullable(false)->change();
            $table->string('payment_card', 50)->nullable(false)->change();
            $table->string('payment_card_type', 50)->nullable(false)->change();
            $table->string('payment_card_brand', 50)->nullable(false)->change();
            $table->string('payment_bank', 100)->nullable(false)->change();
            $table->date('payment_date')->nullable(false)->change();
            $table->date('payment_refund_date')->nullable(false)->change();
            $table->string('payment_refund_desc', 2000)->nullable(false)->change();
            $table->string('payment_status', 50)->nullable(false)->change();
            $table->string('payment_stripe_id', 50)->nullable(false)->change();
            $table->boolean('payment_refund')->nullable(false)->change();
            $table->boolean('used_coupon')->nullable(false)->change();
            $table->string('coupon_id', 255)->nullable(false)->change();
            $table->decimal('coupon_discount', 10, 2)->nullable(false)->change();
        });
    }
};
