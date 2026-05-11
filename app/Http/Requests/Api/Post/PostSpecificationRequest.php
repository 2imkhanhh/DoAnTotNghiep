<?php

namespace App\Http\Requests\Api\Post;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Category;

class PostSpecificationRequest implements ValidationRule
{
    protected $categoryId;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $specs = is_string($value) ? json_decode($value, true) : $value;
        $category = Category::with('attributes')->find($this->categoryId);

        if (!$category) {
            $fail('Danh mục không tồn tại.');
            return;
        }

        foreach ($category->attributes as $attr) {
            if ($attr->is_required) {
                if (!isset($specs[$attr->key]) || $specs[$attr->key] === '') {
                    $fail("Trường '{$attr->name}' là bắt buộc cho danh mục này.");
                }
            }

            if ($attr->type === 'select' && isset($specs[$attr->key])) {
                if (!in_array($specs[$attr->key], $attr->options)) {
                    $fail("Giá trị của trường '{$attr->name}' không hợp lệ.");
                }
            }
        }
    }
}
