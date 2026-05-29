<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thay đổi kiểu cột status sang chuỗi (VARCHAR)
        DB::statement("ALTER TABLE posts MODIFY status VARCHAR(255) DEFAULT 'pending' NOT NULL");

        // 2. Chuyển đổi dữ liệu cũ
        // 0 -> pending, 1 -> active, 2 -> sold (cũ), 3 -> rejected, 4 -> hidden
        DB::table('posts')->where('status', '1')->update(['status' => 'active']);
        DB::table('posts')->where('status', '2')->update(['status' => 'sold']);
        DB::table('posts')->where('status', '3')->update(['status' => 'rejected']);
        DB::table('posts')->where('status', '4')->update(['status' => 'hidden']);
        DB::table('posts')->where('status', '0')->update(['status' => 'pending']);
    }

    public function down(): void
    {
        // Phục hồi lại dữ liệu cũ thành số
        DB::table('posts')->where('status', 'pending')->update(['status' => '0']);
        DB::table('posts')->where('status', 'active')->update(['status' => '1']);
        DB::table('posts')->where('status', 'sold')->update(['status' => '2']);
        DB::table('posts')->where('status', 'rejected')->update(['status' => '3']);
        DB::table('posts')->where('status', 'hidden')->update(['status' => '4']);

        // Đổi lại kiểu cột thành tinyInteger
        DB::statement("ALTER TABLE posts MODIFY status TINYINT DEFAULT 0 NOT NULL");
    }
};
