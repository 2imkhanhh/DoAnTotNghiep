<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_USER = 0;
    const ROLE_ADMIN = 1;

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'address',
        'province_id',
        'province_name',
        'ward_id',
        'ward_name',
        'role',
        'status',
        'rating',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['sold_count', 'reviews_count'];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function getSoldCountAttribute()
    {
        return $this->posts()->where('status', 2)->count();
    }

    public function getReviewsCountAttribute()
    {
        // Hiện tại hệ thống chưa có bảng đánh giá, tạm thời lấy một con số (có thể kết hợp với sold_count) 
        // để hiển thị trên giao diện Public Profile. Ở đây giả lập là 20.
        return 20; 
    }

    public function favoritePosts()
    {
        // Liên kết với bảng Post thông qua bảng trung gian 'favorites'
        return $this->belongsToMany(Post::class, 'favorites', 'user_id', 'post_id')->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
