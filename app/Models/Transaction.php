<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'seller_id',
        'buyer_id',
        'status', // trading, completed, cancelled
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
