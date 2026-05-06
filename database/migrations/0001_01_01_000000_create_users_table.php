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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- CÁC TRƯỜNG BỔ SUNG CHO ĐỒ ÁN ---
            $table->string('avatar')->nullable(); // Lưu đường dẫn ảnh đại diện
            $table->string('address')->nullable(); // Địa chỉ giao dịch

            // Phân quyền: 0 là user thường, 1 là admin (Hoặc dùng enum)
            $table->tinyInteger('role')->default(0)->comment('0: User, 1: Admin');

            // Trạng thái: 1 là hoạt động, 0 là bị khóa
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Locked');

            // (Tùy chọn) Đánh giá uy tín người bán từ 1-5 sao
            $table->decimal('rating', 2, 1)->default(5.0);
            // ------------------------------------

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
