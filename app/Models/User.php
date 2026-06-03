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

    protected $appends = ['sold_count', 'reviews_count', 'average_rating'];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function getSoldCountAttribute()
    {
        return $this->posts()->where('status', 'sold')->count();
    }

    public function getReviewsCountAttribute()
    {
        return $this->receivedReviews()->count();
    }

    public function getAverageRatingAttribute()
    {
        $avg = $this->receivedReviews()->avg('rating');
        return $avg ? round($avg, 1) : 0;
    }

    public function ordersAsSeller()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function ordersAsBuyer()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
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

    // Những người mà user này đang theo dõi
    public function followings()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    // Những người đang theo dõi user này (Người hâm mộ)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    // Hàm kiểm tra xem user hiện tại có đang follow 1 user khác không
    public function isFollowing($userId)
    {
        return $this->followings()->where('followed_id', $userId)->exists();
    }

    /**
     * Các cuộc hội thoại với tư cách là người mua.
     */
    public function buyerConversations()
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    /**
     * Các cuộc hội thoại với tư cách là người bán.
     */
    public function sellerConversations()
    {
        return $this->hasMany(Conversation::class, 'seller_id');
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
