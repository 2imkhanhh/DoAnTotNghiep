<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PostRejectedNotification extends Notification implements ShouldBroadcastNow
{
    public $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_rejected',
            'message' => 'Bài đăng của bạn đã bị từ chối: ' . $this->post->title,
            'post_id' => $this->post->id,
            'url' => '/seller-center/posts?status=rejected'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }
}
