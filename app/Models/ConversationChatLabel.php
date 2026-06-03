<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationChatLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'chat_label_id',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatLabel()
    {
        return $this->belongsTo(ChatLabel::class);
    }
}
