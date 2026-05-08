<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.'
        ];
    }
}
