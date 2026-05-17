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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            // Người đi follow
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            // Người được follow
            $table->foreignId('followed_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Đảm bảo 1 người chỉ có thể follow 1 người khác 1 lần duy nhất
            $table->unique(['follower_id', 'followed_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
