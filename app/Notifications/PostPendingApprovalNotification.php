<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PostPendingApprovalNotification extends Notification implements ShouldBroadcastNow
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
            'type' => 'post_pending',
            'message' => 'Có bài đăng mới cần duyệt: ' . $this->post->title,
            'post_id' => $this->post->id,
            'url' => '/admin/posts'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }
}
