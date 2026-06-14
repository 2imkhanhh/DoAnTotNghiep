<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PostApprovedNotification extends Notification implements ShouldBroadcastNow
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
            'type' => 'post_approved',
            'message' => 'Bài đăng của bạn đã được duyệt: ' . $this->post->title,
            'post_id' => $this->post->id,
            'url' => '/post/' . $this->post->slug
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }
}
