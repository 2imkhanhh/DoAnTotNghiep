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
            'address' => 'nullable|string|max:255',
            'province_id' => 'required|integer',
            'province_name' => 'required|string|max:100',
            'ward_id' => 'required|integer',
            'ward_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/'],
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
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'images.max' => 'Bạn chỉ được tải lên tối đa 6 hình ảnh.',
            'images.*.image' => 'File tải lên phải là định dạng hình ảnh.',
        ];
    }
}
