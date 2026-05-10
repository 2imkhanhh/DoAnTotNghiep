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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Đường dẫn chuẩn SEO (VD: dien-thoai)
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('icon')->nullable(); // Lưu đường dẫn hình ảnh icon
            $table->boolean('is_active')->default(true); // Trạng thái Ẩn/Hiện
            $table->timestamps();

            // Khóa ngoại tự trỏ vào chính bảng categories
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
