<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // Chìa khoá cho LocalStorage
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Dành cho user đã đăng nhập
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};
