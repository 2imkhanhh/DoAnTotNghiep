<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $conversationId;
    public $buyerId;
    public $sellerId;

    /**
     * Create a new event instance.
     */
    public function __construct($messageId, $conversation)
    {
        $this->messageId = $messageId;
        $this->conversationId = $conversation->id;
        $this->buyerId = $conversation->buyer_id;
        $this->sellerId = $conversation->seller_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->conversationId),
            new PrivateChannel('App.Models.User.' . $this->buyerId),
            new PrivateChannel('App.Models.User.' . $this->sellerId),
        ];
    }

    /**
     * Tên sự kiện broadcast được lắng nghe ở frontend.
     */
    public function broadcastAs(): string
    {
        return 'message.deleted';
    }
}
