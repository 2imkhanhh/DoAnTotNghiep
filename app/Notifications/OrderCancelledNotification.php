<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderCancelledNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $order;
    protected $cancelledBy; // 'buyer' or 'seller'

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, $cancelledBy = 'buyer')
    {
        $this->order = $order;
        $this->cancelledBy = $cancelledBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $cancelerRole = $this->cancelledBy === 'buyer' ? 'Người mua' : 'Người bán';
        return [
            'type' => 'order_cancelled',
            'message' => $cancelerRole . ' đã huỷ đơn hàng từ bài đăng: ' . $this->order->post->title,
            'order_id' => $this->order->id,
            'post_id' => $this->order->post_id,
            // Nếu người huỷ là buyer thì gửi cho seller -> url tới trang quản lý đơn hàng của seller
            // Nếu người huỷ là seller thì gửi cho buyer -> url tới trang đơn mua của buyer
            'url' => $this->cancelledBy === 'buyer' ? '/seller-center/orders' : '/my-orders'
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }
}
