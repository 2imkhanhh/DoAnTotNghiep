<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color_code',
        'is_default',
    ];

    /**
     * Get the user that owns the label.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the conversation labels associated with this label.
     */
    public function conversationLabels()
    {
        return $this->hasMany(ConversationChatLabel::class);
    }
}
