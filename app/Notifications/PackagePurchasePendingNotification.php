<?php

namespace App\Notifications;

use App\Models\UserPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PackagePurchasePendingNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $purchase;

    public function __construct(UserPurchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'package_pending',
            'message' => 'Có yêu cầu mua gói "' . $this->purchase->package->name . '" cần duyệt từ ' . $this->purchase->user->name,
            'purchase_id' => $this->purchase->id,
            'url' => '/admin/purchases'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }
}
