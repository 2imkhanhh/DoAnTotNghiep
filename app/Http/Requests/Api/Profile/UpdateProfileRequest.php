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
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'address' => 'sometimes|nullable|string|max:255',
            'province_id' => 'sometimes|nullable|integer',
            'province_name' => 'sometimes|nullable|string|max:100',
            'ward_id' => 'sometimes|nullable|integer',
            'ward_name' => 'sometimes|nullable|string|max:100',
            'avatar' => 'sometimes|nullable|string',
        ];
    }
}
