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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // 1. Khóa ngoại liên kết
            $table->unsignedBigInteger('user_id'); // Ai là người đăng?
            $table->unsignedBigInteger('category_id'); // Thuộc danh mục nào?

            // 2. Thông tin cơ bản (Tin nào cũng phải có)
            $table->string('title'); // Tiêu đề tin
            $table->string('slug')->unique(); // Đường dẫn
            $table->longText('description'); // Mô tả chi tiết
            $table->decimal('price', 15, 2)->default(0); // Giá tiền
            $table->string('address'); // Địa chỉ giao dịch
            $table->string('phone'); // Số điện thoại liên hệ

            // 3. VŨ KHÍ BÍ MẬT: Cột JSON lưu thông tin động theo danh mục
            $table->json('specifications')->nullable();

            // 4. Trạng thái tin đăng
            $table->tinyInteger('status')->default(0); // 0: Chờ duyệt, 1: Đang hiển thị, 2: Đã bán, 3: Bị từ chối

            $table->timestamps();

            // Thiết lập Khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};