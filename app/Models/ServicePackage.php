<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'price',
        'duration_days',
        'post_quota',
        'is_active',
    ];

    public function purchases()
    {
        return $this->hasMany(UserPurchase::class, 'package_id');
    }
}
