<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendResetLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email'
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'Email này chưa được đăng ký trong hệ thống.'
        ];
    }
}
