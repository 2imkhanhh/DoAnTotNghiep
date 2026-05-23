<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $fillable = ['session_id', 'user_id'];

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
