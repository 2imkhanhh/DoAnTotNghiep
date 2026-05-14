<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id', // parent_id phải tồn tại trong bảng
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048', // Nhận cả file SVG/PNG cho icon
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ];
    }
}
