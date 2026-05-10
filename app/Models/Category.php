<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'icon',
        'is_active'
    ];

    // Lấy ra danh sách các Danh mục con của nó
    public function children()
    {
        // Câu này dịch ra tiếng Việt: 
        // "Tao có nhiều (hasMany) đứa con ở trong bảng Category. 
        // Mày cứ lấy ID của tao, đem đi so sánh với cột 'parent_id' của bọn nó là tìm ra."
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Lấy ra Danh mục cha của nó
    public function parent()
    {
        // Câu này dịch ra tiếng Việt: 
        // "Tao thuộc về (belongsTo) một thằng cha trong bảng Category. 
        // Mày cứ lấy cái 'parent_id' của tao, đi dò xem thằng nào có 'id' khớp với số đó thì lấy ra."
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
