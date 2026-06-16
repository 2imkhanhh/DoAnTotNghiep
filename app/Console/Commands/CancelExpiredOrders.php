<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Hủy các đơn hàng chờ thanh toán QR quá 15 phút để mở khóa bài đăng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lấy thời điểm 15 phút trước
        $threshold = Carbon::now()->subMinutes(15);

        // Tìm các đơn hàng awaiting_payment tạo trước mốc thời gian này
        $expiredOrders = Order::where('status', 'awaiting_payment')
            ->where('created_at', '<', $threshold)
            ->get();

        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$count) {
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
