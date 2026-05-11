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
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name'); // Tên hiển thị (VD: Dung lượng)
            $table->string('key');  // Key lưu trong JSON (VD: storage)
            $table->string('type'); // text, number, select, checkbox...
            $table->json('options')->nullable(); // Lưu các lựa chọn nếu type là select
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_attributes');
    }
};
