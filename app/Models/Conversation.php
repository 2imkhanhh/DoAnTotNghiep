<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'post_id',
    ];

    /**
     * Người mua trong cuộc trò chuyện này.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Người bán trong cuộc trò chuyện này.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Bài đăng liên quan đến cuộc trò chuyện này.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Các tin nhắn thuộc cuộc trò chuyện này.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Tin nhắn mới nhất trong cuộc trò chuyện.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Nhãn được gán cho cuộc trò chuyện bởi người dùng hiện tại
     */
    public function userLabels()
    {
        return $this->hasMany(ConversationChatLabel::class, 'conversation_id')
                    ->where('user_id', auth('api')->id())
                    ->with('chatLabel'); // Eager load luôn label object
    }
}
