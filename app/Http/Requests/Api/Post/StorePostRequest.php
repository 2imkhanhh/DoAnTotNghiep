<?php

namespace App\Http\Requests\Api\Post;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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

            // Specifications sử dụng Custom Rule để validate động theo danh mục
            'specifications' => [
                'nullable',
                new PostSpecificationRequest($this->input('category_id'))
            ],

            // Kiểm tra mảng hình ảnh (Bắt buộc có ít nhất 1 ảnh, tối đa 6 ảnh)
            'images' => 'required|array|min:1|max:6',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048', // Mỗi ảnh tối đa 2MB
        ];
    }

    public function messages()
    {
        return [
            'images.required' => 'Vui lòng tải lên ít nhất 1 hình ảnh.',
            'images.max' => 'Bạn chỉ được tải lên tối đa 6 hình ảnh.',
            'images.*.image' => 'File tải lên phải là định dạng hình ảnh.',
        ];
    }
}
