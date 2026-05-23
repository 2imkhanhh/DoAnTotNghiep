<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_session_id')->constrained('chatbot_sessions')->onDelete('cascade');
            $table->string('role'); // 'user' hoặc 'model' (AI)
            $table->text('content'); // Nội dung chat
            $table->json('data')->nullable(); // Chứa thông tin sản phẩm trả về
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
