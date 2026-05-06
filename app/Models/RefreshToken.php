<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    // Tạo liên kết ngược lại với bảng User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
