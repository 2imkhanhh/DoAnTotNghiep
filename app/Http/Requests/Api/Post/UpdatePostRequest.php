<?php

namespace App\Http\Requests\Api\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'province_id' => 'required|integer',
            'province_name' => 'required|string|max:100',
            'ward_id' => 'required|integer',
            'ward_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'category_id' => 'required|exists:categories,id',

            'specifications' => [
                'nullable',
                new PostSpecificationRequest($this->input('category_id'))
            ],

            // Đối với cập nhật, hình ảnh là không bắt buộc (nullable)
            'images' => 'nullable|array|max:6',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'images.max' => 'Bạn chỉ được tải lên tối đa 6 hình ảnh.',
            'images.*.image' => 'File tải lên phải là định dạng hình ảnh.',
        ];
    }
}
