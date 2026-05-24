<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\ChatbotSession;

// Lên lịch tự động dọn dẹp các phiên chat của khách vãng lai (không đăng nhập) sau 2 ngày không hoạt động
Schedule::call(function () {
    $threshold = now()->subDays(2);
    $oldSessions = ChatbotSession::whereNull('user_id')
        ->where('updated_at', '<', $threshold)
        ->get();

    foreach ($oldSessions as $session) {
        // Xóa tin nhắn trước
        $session->messages()->delete();
        // Xóa phiên làm việc
        $session->delete();
    }
})->daily()->description('Dọn dẹp rác Chatbot từ khách vãng lai');
