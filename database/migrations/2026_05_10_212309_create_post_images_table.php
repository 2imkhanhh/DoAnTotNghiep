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
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại trỏ về bảng posts
            $table->unsignedBigInteger('post_id');

            // Đường dẫn lưu ảnh trong thư mục storage
            $table->string('image_path');

            // (Tùy chọn) Đánh dấu ảnh nào là ảnh bìa chính (thumbnail)
            // Nếu is_primary = true thì ảnh đó sẽ hiện ngoài trang chủ
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            // Ràng buộc khóa ngoại: Khi xóa tin đăng, toàn bộ ảnh của tin đó tự động bay màu theo
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
