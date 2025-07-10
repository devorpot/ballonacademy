<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('used_coupon')->default(false)->after('course_id');
            $table->string('coupon_id')->nullable()->after('used_coupon');
            $table->integer('coupon_discount')->default(0)->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['used_coupon', 'coupon_id', 'coupon_discount']);
        });
    }
};
