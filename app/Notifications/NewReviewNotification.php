<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewReviewNotification extends Notification implements ShouldBroadcastNow
{
    public $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_review',
            'message' => 'Bạn có một đánh giá mới từ ' . $this->review->reviewer->name,
            'review_id' => $this->review->id,
            'url' => '/seller/' . $this->review->reviewed_user_id
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }
}
