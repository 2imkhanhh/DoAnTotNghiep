<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $conversationId;

    public function __construct(Transaction $transaction, $conversationId)
    {
        $this->transaction = $transaction;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn()
    {
        // Phát sự kiện vào channel chat để chat.js có thể lắng nghe
        return new PrivateChannel('chat.' . $this->conversationId);
    }
}
