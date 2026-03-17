<?php

namespace App\Http\Requests\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpertiseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'expertise_ids'   => ['required', 'array'],
            'expertise_ids.*' => ['integer', 'exists:expertises,expertise_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'expertise_ids.required' => 'Vui lòng chọn ít nhất một chuyên môn.',
            'expertise_ids.array'    => 'Dữ liệu chuyên môn không hợp lệ.',
            'expertise_ids.*.exists' => 'Chuyên môn không tồn tại.',
        ];
    }
}