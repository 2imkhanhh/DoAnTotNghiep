<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message_text',
        'image_path',
        'is_read',
        'post_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Cuộc trò chuyện chứa tin nhắn này.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * Người gửi tin nhắn này.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    /**
     * Bài đăng đính kèm tin nhắn này.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
