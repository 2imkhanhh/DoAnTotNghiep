<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'address',
        'province_id',
        'province_name',
        'ward_id',
        'ward_name',
        'phone',
        'specifications',
        'status',
        'reject_reason'
    ];

    // Ép kiểu dữ liệu (Cực kỳ quan trọng)
    protected $casts = [
        // Khi lấy từ DB ra, Laravel tự biến chuỗi JSON thành Array cho dễ dùng
        'specifications' => 'array',
        'price' => 'decimal:2'
    ];

    // Mối quan hệ: Một tin đăng thuộc về MỘT người dùng
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mối quan hệ: Một tin đăng thuộc về MỘT danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }
}
