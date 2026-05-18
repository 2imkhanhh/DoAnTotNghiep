<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryAttributeRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'key' => 'sometimes|required|string|alpha_dash',
            'type' => 'sometimes|required|in:text,number,select,checkbox,radio',

            // Dùng required_if: Nếu type là select, checkbox hoặc radio thì BẮT BUỘC phải có mảng options
            'options' => 'required_if:type,select,checkbox,radio|array|nullable',
            'options.*' => 'string',

            'is_required' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'options.required_if' => 'Kiểu select, checkbox hoặc radio thì bắt buộc phải nhập danh sách các tùy chọn (options).',
            'key.alpha_dash' => 'Key chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới (không có khoảng trắng hay tiếng Việt có dấu).'
        ];
    }
}
