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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            // Liên kết với bảng users, nếu user bị xóa thì token cũng bay theo
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Chuỗi token ngẫu nhiên, để duy nhất (unique) để query cho nhanh
            $table->string('token', 100)->unique(); 
            
            // Thời gian hết hạn của Refresh Token
            $table->timestamp('expires_at'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
