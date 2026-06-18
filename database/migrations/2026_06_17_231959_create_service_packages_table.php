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
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['vip', 'post'])->comment('vip: gói tính theo ngày, post: gói cộng lượt đăng');
            $table->decimal('price', 15, 2);
            $table->integer('duration_days')->nullable()->comment('Số ngày hiệu lực (chỉ dành cho gói VIP)');
            $table->integer('post_quota')->nullable()->comment('Số lượt đăng tin cộng thêm (dành cho gói post)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
