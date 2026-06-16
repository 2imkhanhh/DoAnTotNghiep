<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hủy các đơn hàng chờ thanh toán QR quá 5 phút để mở khóa bài đăng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lấy thời điểm 5 phút trước
        $threshold = Carbon::now()->subMinutes(5);

        // Tìm các đơn hàng awaiting_payment tạo trước mốc thời gian này
        $expiredOrders = Order::where('status', 'awaiting_payment')
            ->where('created_at', '<', $threshold)
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$count) {
                // Tăng biến đếm số lần quá hạn thanh toán của user cho sản phẩm này
                $cacheKey = 'qr_timeout_' . $order->buyer_id . '_' . $order->post_id;
                $timeouts = Cache::get($cacheKey, 0) + 1;
                // Lưu lại số lần vi phạm trong 24 giờ
                Cache::put($cacheKey, $timeouts, now()->addHours(24));

                // Xóa đơn hàng hoàn toàn khỏi cơ sở dữ liệu
                $order->delete();

                // Khôi phục bài đăng
                $post = Post::find($order->post_id);
                if ($post && $post->status === 'hidden') {
                    $post->update(['status' => 'active']);
                }

                $count++;
            });
        }

        if ($count > 0) {
            $this->info("Đã hủy thành công {$count} đơn hàng chờ thanh toán quá hạn.");
            Log::info("Đã hủy tự động {$count} đơn hàng awaiting_payment hết hạn.");
        } else {
            $this->info("Không có đơn hàng nào quá hạn.");
        }
    }
}
