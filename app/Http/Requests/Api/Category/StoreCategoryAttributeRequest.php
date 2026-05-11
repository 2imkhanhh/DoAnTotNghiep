<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryAttributeRequest extends FormRequest
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
            'key' => 'required|string|alpha_dash',
            'type' => 'required|in:text,number,select,radio',

            // Dùng required_if: Nếu type là select hoặc radio thì BẮT BUỘC phải có mảng options
            'options' => 'required_if:type,select,radio|array|nullable',
            'options.*' => 'string',

            'is_required' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'options.required_if' => 'Kiểu select hoặc radio thì bắt buộc phải nhập danh sách các tùy chọn (options).',
            'key.alpha_dash' => 'Key chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới (không có khoảng trắng hay tiếng Việt có dấu).'
        ];
    }
}
