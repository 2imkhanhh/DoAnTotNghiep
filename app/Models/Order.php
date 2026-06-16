<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'seller_id',
        'buyer_id',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_province_id',
        'shipping_ward_id',
        'shipping_note',
        'total_price',
        'status', // pending, shipping, delivered, cancelled, rejected
        'payment_method',
        'payment_status',
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
