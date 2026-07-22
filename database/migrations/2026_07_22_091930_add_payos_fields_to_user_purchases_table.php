<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_purchases', function (Blueprint $table) {
            $table->string('payment_method')->default('manual');
            $table->bigInteger('payos_order_code')->nullable()->unique();
            $table->string('checkout_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payos_order_code', 'checkout_url']);
        });
    }
};
