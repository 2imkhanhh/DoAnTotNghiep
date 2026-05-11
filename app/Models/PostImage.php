<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'image_path',
        'is_primary'
    ];

    // Mối quan hệ: Một hình ảnh thuộc về MỘT tin đăng
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
