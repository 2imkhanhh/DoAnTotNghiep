<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth('api')->id();
        
        return [
            'name' => 'sometimes|string|max:255',
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^0[0-9]{9}$/',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'address' => 'sometimes|nullable|string|max:255',
            'province_id' => 'sometimes|nullable|integer',
            'province_name' => 'sometimes|nullable|string|max:100',
            'ward_id' => 'sometimes|nullable|integer',
            'ward_name' => 'sometimes|nullable|string|max:100',
            'avatar' => 'sometimes|nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
        ];
    }
}
